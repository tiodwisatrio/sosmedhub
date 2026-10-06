<?php

namespace Modules\Scheduler\Services;

use RuntimeException;

/**
 * Membaca metadata video MP4/MOV langsung dari struktur box-nya (ISO BMFF), tanpa ffprobe.
 * Cukup untuk memeriksa syarat Instagram sebelum video diunggah: durasi, ukuran tampilan
 * (termasuk rotasi), codec video dan audio, frame rate, dan apakah moov berada di depan file.
 *
 * Hanya membaca kerangka file: file besar tidak dimuat ke memori, hanya kotak moov.
 */
class Mp4Inspector
{
    private const MAX_MOOV_BYTES = 64 * 1024 * 1024;

    private const MAX_TOP_LEVEL_BOXES = 200000;

    // Total byte metadata fragmen (moof) yang dibaca; video fragmented panjang punya ratusan moof kecil.
    private const MAX_FRAGMENT_BYTES = 64 * 1024 * 1024;

    private const VIDEO_CODECS = [
        'avc1' => 'h264', 'avc3' => 'h264',
        'hvc1' => 'hevc', 'hev1' => 'hevc',
    ];

    /**
     * @return array{
     *     duration: float, width: int, height: int, video_codec: ?string, audio_codec: ?string,
     *     has_audio: bool, fps: ?float, faststart: bool, brand: ?string
     * }
     */
    public function inspect(string $path): array
    {
        $handle = @fopen($path, 'rb');

        if (! $handle) {
            throw new RuntimeException('File video tidak bisa dibuka.');
        }

        try {
            return $this->read($handle, (int) filesize($path));
        } finally {
            fclose($handle);
        }
    }

    /**
     * @param  resource  $handle
     */
    private function read($handle, int $fileSize): array
    {
        $brand = null;
        $moov = null;
        $moovOffset = null;
        $firstMdat = null;
        $fragments = [];
        $fragmentBytes = 0;
        $offset = 0;

        for ($i = 0; $i < self::MAX_TOP_LEVEL_BOXES && $offset + 8 <= $fileSize; $i++) {
            fseek($handle, $offset);
            $header = fread($handle, 16);

            if (strlen($header) < 8) {
                break;
            }

            $size = unpack('N', substr($header, 0, 4))[1];
            $type = substr($header, 4, 4);
            $headerSize = 8;

            if ($size === 1) {
                if (strlen($header) < 16) {
                    break;
                }

                $size = unpack('J', substr($header, 8, 8))[1];
                $headerSize = 16;
            } elseif ($size === 0) {
                $size = $fileSize - $offset;
            }

            if ($size < $headerSize || $offset + $size > $fileSize + 8) {
                throw new RuntimeException('Struktur file video rusak atau terpotong.');
            }

            if ($type === 'ftyp' && $brand === null) {
                fseek($handle, $offset + $headerSize);
                $brand = rtrim((string) fread($handle, 4));
            } elseif ($type === 'mdat' && $firstMdat === null) {
                $firstMdat = $offset;
            } elseif ($type === 'moof') {
                // MP4 fragmented: durasi dan jumlah sampel ada di tiap moof, bukan di moov.
                $fragmentBytes += $size - $headerSize;

                if ($fragmentBytes > self::MAX_FRAGMENT_BYTES) {
                    throw new RuntimeException('Metadata fragmen video terlalu besar untuk dibaca.');
                }

                fseek($handle, $offset + $headerSize);
                $fragments[] = (string) fread($handle, $size - $headerSize);
            } elseif ($type === 'moov' && $moov === null) {
                $length = $size - $headerSize;

                if ($length > self::MAX_MOOV_BYTES) {
                    throw new RuntimeException('Metadata video terlalu besar untuk dibaca.');
                }

                fseek($handle, $offset + $headerSize);
                $moov = (string) fread($handle, $length);
                $moovOffset = $offset;
            }

            $offset += $size;
        }

        if ($moov === null) {
            throw new RuntimeException('File ini bukan video MP4/MOV yang valid (metadata moov tidak ditemukan).');
        }

        return $this->summarize($moov, $fragments) + [
            'faststart' => $firstMdat === null || $moovOffset < $firstMdat,
            'brand' => $brand,
        ];
    }

    /**
     * @param  list<string>  $fragments  isi tiap kotak moof (kosong untuk MP4 biasa)
     */
    private function summarize(string $moov, array $fragments = []): array
    {
        $duration = null;
        $video = null;
        $audio = null;
        $movieTimescale = null;
        $fragmentDuration = null;
        $trexDurations = [];

        foreach ($this->boxes($moov, 0, strlen($moov)) as [$type, $start, $end]) {
            if ($type === 'mvhd') {
                $duration = $this->timescaleDuration($moov, $start);
                $movieTimescale = $this->movieTimescale($moov, $start);
            } elseif ($type === 'mvex') {
                [$fragmentDuration, $trexDurations] = $this->movieExtends($moov, $start, $end);
            } elseif ($type === 'trak') {
                $track = $this->track($moov, $start, $end);

                if ($track['handler'] === 'vide' && $video === null) {
                    $video = $track;
                } elseif ($track['handler'] === 'soun' && $audio === null) {
                    $audio = $track;
                }
            }
        }

        if ($video === null) {
            throw new RuntimeException('Tidak ada jalur video di file ini.');
        }

        $seconds = $duration ?: ($video['duration'] ?? null);
        $samples = $video['samples'];

        // MP4 fragmented: moov menulis durasi 0. Ambil dari mehd, atau jumlahkan durasi sampel di tiap fragmen.
        if ((! $seconds || $seconds <= 0) && $fragments !== []) {
            if ($fragmentDuration && $movieTimescale) {
                $seconds = $fragmentDuration / $movieTimescale;
            }

            if ($video['id'] !== null && $video['timescale']) {
                [$ticks, $fragmentSamples] = $this->fragmentTotals($fragments, $video['id'], $trexDurations[$video['id']] ?? 0);

                if ($ticks > 0) {
                    $seconds = $ticks / $video['timescale'];
                    $samples = $fragmentSamples;
                    $video['duration'] = $seconds;
                }
            }
        }

        if (! $seconds || $seconds <= 0) {
            throw new RuntimeException('Durasi video tidak bisa dibaca. Ekspor ulang videonya sebagai MP4 standar.');
        }

        $fps = $samples && $seconds ? $samples / ($video['duration'] ?: $seconds) : null;

        return [
            'duration' => round($seconds, 3),
            'width' => $video['width'],
            'height' => $video['height'],
            'video_codec' => self::VIDEO_CODECS[$video['codec']] ?? $video['codec'],
            'audio_codec' => $audio ? ($audio['codec'] === 'mp4a' ? 'aac' : $audio['codec']) : null,
            'has_audio' => $audio !== null,
            'fps' => $fps ? round($fps, 2) : null,
        ];
    }

    /**
     * @return array{id: ?int, handler: ?string, codec: ?string, width: int, height: int, duration: ?float, timescale: ?int, samples: int}
     */
    private function track(string $data, int $start, int $end): array
    {
        $track = ['id' => null, 'handler' => null, 'codec' => null, 'width' => 0, 'height' => 0, 'duration' => null, 'timescale' => null, 'samples' => 0];

        foreach ($this->boxes($data, $start, $end) as [$type, $s, $e]) {
            if ($type === 'tkhd') {
                [$track['width'], $track['height']] = $this->displaySize($data, $s);
                $version = ord($data[$s]);
                $track['id'] = unpack('N', substr($data, $s + ($version === 1 ? 20 : 12), 4))[1];
            } elseif ($type === 'mdia') {
                foreach ($this->boxes($data, $s, $e) as [$mType, $ms, $me]) {
                    if ($mType === 'mdhd') {
                        $track['duration'] = $this->timescaleDuration($data, $ms);
                        $track['timescale'] = $this->movieTimescale($data, $ms);
                    } elseif ($mType === 'hdlr') {
                        $track['handler'] = substr($data, $ms + 8, 4);
                    } elseif ($mType === 'minf') {
                        $this->sampleTable($data, $ms, $me, $track);
                    }
                }
            }
        }

        return $track;
    }

    private function sampleTable(string $data, int $start, int $end, array &$track): void
    {
        foreach ($this->boxes($data, $start, $end) as [$type, $s, $e]) {
            if ($type !== 'stbl') {
                continue;
            }

            foreach ($this->boxes($data, $s, $e) as [$sType, $ss, $se]) {
                if ($sType === 'stsd' && $se - $ss >= 16) {
                    // 4 byte versi/flag, 4 byte jumlah entri, lalu entri pertama: ukuran + tipe.
                    $track['codec'] = substr($data, $ss + 12, 4);
                } elseif ($sType === 'stts') {
                    $entries = unpack('N', substr($data, $ss + 4, 4))[1];
                    $entries = min($entries, intdiv($se - $ss - 8, 8));

                    for ($i = 0; $i < $entries; $i++) {
                        $track['samples'] += unpack('N', substr($data, $ss + 8 + $i * 8, 4))[1];
                    }
                }
            }
        }
    }

    /**
     * Ukuran tampilan setelah rotasi: video iPhone potret disimpan landscape dengan rotasi 90 derajat.
     *
     * @return array{0: int, 1: int}
     */
    private function displaySize(string $data, int $start): array
    {
        $version = ord($data[$start]);
        $matrixAt = $start + ($version === 1 ? 52 : 40);
        $sizeAt = $matrixAt + 36;

        if (strlen($data) < $sizeAt + 8) {
            return [0, 0];
        }

        // Matriks MP4 big-endian bertanda; unpack('l') memakai urutan byte mesin, jadi dibaca manual.
        $m = [];
        for ($i = 0; $i < 9; $i++) {
            $value = unpack('N', substr($data, $matrixAt + $i * 4, 4))[1];
            $m[] = $value >= 0x80000000 ? $value - 0x100000000 : $value;
        }

        $width = (int) round(unpack('N', substr($data, $sizeAt, 4))[1] / 65536);
        $height = (int) round(unpack('N', substr($data, $sizeAt + 4, 4))[1] / 65536);

        [$a, $b, , $c, $d] = $m;
        $rotated = $a === 0 && $d === 0 && $b !== 0 && $c !== 0;

        return $rotated ? [$height, $width] : [$width, $height];
    }

    /**
     * Timescale dari kotak mvhd atau mdhd (satuan waktu per detik).
     */
    private function movieTimescale(string $data, int $start): ?int
    {
        $version = ord($data[$start]);
        $timescale = unpack('N', substr($data, $start + ($version === 1 ? 20 : 12), 4))[1];

        return $timescale > 0 ? $timescale : null;
    }

    /**
     * Isi kotak mvex: durasi seluruh fragmen (mehd, satuan timescale film) dan durasi sampel bawaan
     * per jalur (trex).
     *
     * @return array{0: ?int, 1: array<int, int>}
     */
    private function movieExtends(string $data, int $start, int $end): array
    {
        $fragmentDuration = null;
        $defaults = [];

        foreach ($this->boxes($data, $start, $end) as [$type, $s, $e]) {
            if ($type === 'mehd' && $e - $s >= 8) {
                $version = ord($data[$s]);
                $fragmentDuration = $version === 1 && $e - $s >= 12
                    ? unpack('J', substr($data, $s + 4, 8))[1]
                    : unpack('N', substr($data, $s + 4, 4))[1];
            } elseif ($type === 'trex' && $e - $s >= 20) {
                // versi/flag, track_ID, deskripsi sampel, durasi bawaan, ukuran bawaan, flag bawaan
                $fields = unpack('Ntrack/Ndescription/Nduration', substr($data, $s + 4, 12));
                $defaults[$fields['track']] = $fields['duration'];
            }
        }

        return [$fragmentDuration, $defaults];
    }

    /**
     * Menjumlahkan durasi (satuan timescale jalur) dan jumlah sampel satu jalur dari semua fragmen.
     *
     * @param  list<string>  $fragments  isi tiap moof
     * @return array{0: int, 1: int} [total durasi, total sampel]
     */
    private function fragmentTotals(array $fragments, int $trackId, int $trexDuration): array
    {
        $ticks = 0;
        $samples = 0;

        foreach ($fragments as $moof) {
            foreach ($this->boxes($moof, 0, strlen($moof)) as [$type, $start, $end]) {
                if ($type !== 'traf') {
                    continue;
                }

                $defaultDuration = $trexDuration;
                $matches = false;

                foreach ($this->boxes($moof, $start, $end) as [$childType, $s, $e]) {
                    if ($childType === 'tfhd' && $e - $s >= 8) {
                        $flags = unpack('N', substr($moof, $s, 4))[1] & 0xFFFFFF;
                        $matches = unpack('N', substr($moof, $s + 4, 4))[1] === $trackId;
                        $at = $s + 8;

                        if ($flags & 0x1) {
                            $at += 8; // base_data_offset
                        }

                        if ($flags & 0x2) {
                            $at += 4; // sample_description_index
                        }

                        if ($flags & 0x8 && $at + 4 <= $e) {
                            $defaultDuration = unpack('N', substr($moof, $at, 4))[1];
                        }
                    } elseif ($childType === 'trun' && $matches && $e - $s >= 8) {
                        [$trunTicks, $count] = $this->trunTotals($moof, $s, $e, $defaultDuration);
                        $ticks += $trunTicks;
                        $samples += $count;
                    }
                }
            }
        }

        return [$ticks, $samples];
    }

    /**
     * @return array{0: int, 1: int} [durasi, jumlah sampel] dari satu kotak trun
     */
    private function trunTotals(string $data, int $start, int $end, int $defaultDuration): array
    {
        $flags = unpack('N', substr($data, $start, 4))[1] & 0xFFFFFF;
        $count = unpack('N', substr($data, $start + 4, 4))[1];
        $at = $start + 8;

        if ($flags & 0x1) {
            $at += 4; // data_offset
        }

        if ($flags & 0x4) {
            $at += 4; // first_sample_flags
        }

        $hasDuration = (bool) ($flags & 0x100);

        if (! $hasDuration) {
            return [$count * $defaultDuration, $count];
        }

        // Per sampel: durasi, lalu (bila ada) ukuran, flag, dan selisih komposisi.
        $stride = 4 + (($flags & 0x200) ? 4 : 0) + (($flags & 0x400) ? 4 : 0) + (($flags & 0x800) ? 4 : 0);
        $count = min($count, intdiv($end - $at, $stride));
        $ticks = 0;

        for ($i = 0; $i < $count; $i++) {
            $ticks += unpack('N', substr($data, $at + $i * $stride, 4))[1];
        }

        return [$ticks, $count];
    }

    private function timescaleDuration(string $data, int $start): ?float
    {
        $version = ord($data[$start]);

        if ($version === 1) {
            $timescale = unpack('N', substr($data, $start + 20, 4))[1];
            $duration = unpack('J', substr($data, $start + 24, 8))[1];
        } else {
            $timescale = unpack('N', substr($data, $start + 12, 4))[1];
            $duration = unpack('N', substr($data, $start + 16, 4))[1];
        }

        return $timescale > 0 ? $duration / $timescale : null;
    }

    /**
     * Anak-anak box dalam rentang [start, end): tipe, awal isi, akhir isi.
     *
     * @return \Generator<int, array{0: string, 1: int, 2: int}>
     */
    private function boxes(string $data, int $start, int $end): \Generator
    {
        $offset = $start;
        $end = min($end, strlen($data));

        while ($offset + 8 <= $end) {
            $size = unpack('N', substr($data, $offset, 4))[1];
            $type = substr($data, $offset + 4, 4);
            $header = 8;

            if ($size === 1 && $offset + 16 <= $end) {
                $size = unpack('J', substr($data, $offset + 8, 8))[1];
                $header = 16;
            } elseif ($size === 0) {
                $size = $end - $offset;
            }

            if ($size < $header || $offset + $size > $end) {
                return;
            }

            yield [$type, $offset + $header, $offset + $size];

            $offset += $size;
        }
    }
}
