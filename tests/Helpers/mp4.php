<?php

/**
 * Membangun MP4 minimal (hanya kerangka box, tanpa data video) untuk menguji pembaca.
 */
function mp4Box(string $type, string $payload): string
{
    return pack('N', 8 + strlen($payload)).$type.$payload;
}

function mp4Track(string $handler, string $codec, int $width, int $height, int $timescale, int $duration, int $samples = 0, bool $rotate = false, int $trackId = 1): string
{
    $matrix = $rotate
        ? pack('N*', 0, 0x10000, 0, 0xFFFF0000, 0, 0, 0, 0, 0x40000000)
        : pack('N*', 0x10000, 0, 0, 0, 0x10000, 0, 0, 0, 0x40000000);

    // versi/flag, dibuat, diubah, track_ID, cadangan, durasi
    $tkhd = pack('N', 0).pack('N', 0).pack('N', 0).pack('N', $trackId).pack('N', 0).pack('N', 0).str_repeat("\0", 8)
        .str_repeat("\0", 8).$matrix.pack('N', $width << 16).pack('N', $height << 16);
    $mdhd = pack('N', 0).pack('N', 0).pack('N', 0).pack('N', $timescale).pack('N', $duration).pack('n', 0).pack('n', 0);
    $hdlr = pack('N', 0).pack('N', 0).$handler.str_repeat("\0", 12)."x\0";
    $stsd = pack('N', 0).pack('N', 1).pack('N', 8 + 78).$codec.str_repeat("\0", 78);
    $stts = pack('N', 0).pack('N', 1).pack('N', $samples).pack('N', $samples ? intdiv($duration, $samples) : 0);

    $stbl = mp4Box('stsd', $stsd).mp4Box('stts', $stts);

    return mp4Box('trak', mp4Box('tkhd', $tkhd).mp4Box('mdia',
        mp4Box('mdhd', $mdhd).mp4Box('hdlr', $hdlr).mp4Box('minf', mp4Box('stbl', $stbl))
    ));
}

function fakeMp4(
    float $seconds = 10,
    int $width = 1080,
    int $height = 1920,
    string $videoCodec = 'avc1',
    ?string $audioCodec = 'mp4a',
    bool $faststart = true,
    bool $rotate = false,
    float $fps = 30,
    string $brand = 'isom',
    bool $largeMdat = false,
): string {
    $timescale = 1000;
    $duration = (int) round($seconds * $timescale);
    $mvhd = pack('N', 0).pack('N', 0).pack('N', 0).pack('N', $timescale).pack('N', $duration).str_repeat("\0", 80);

    $moov = mp4Box('moov',
        mp4Box('mvhd', $mvhd)
        .mp4Track('vide', $videoCodec, $width, $height, $timescale, $duration, (int) round($fps * $seconds), $rotate)
        .($audioCodec ? mp4Track('soun', $audioCodec, 0, 0, $timescale, $duration) : '')
    );

    $ftyp = mp4Box('ftyp', $brand.pack('N', 0).$brand);
    $mdat = $largeMdat
        ? pack('N', 1).'mdat'.pack('J', 16 + 32).str_repeat("\1", 32)
        : mp4Box('mdat', str_repeat("\1", 32));

    return $ftyp.($faststart ? $moov.$mdat : $mdat.$moov);
}

function mp4File(string $content): string
{
    $path = tempnam(sys_get_temp_dir(), 'mp4');
    file_put_contents($path, $content);

    return $path;
}

/**
 * MP4 fragmented: moov berdurasi 0, durasi sebenarnya di moof/trun (atau bawaan tfhd/trex/mehd).
 *
 * @param  string  $durationIn  'trun', 'tfhd' (bawaan di tfhd), 'trex' (bawaan di trex), atau 'none'
 */
function fakeFragmentedMp4(
    float $seconds = 8,
    float $fps = 30,
    int $width = 1080,
    int $height = 1920,
    string $durationIn = 'trun',
    bool $withMehd = false,
    int $fragments = 3,
    int $timescale = 15360,
    bool $withAudioFragments = false,
): string {
    $perSample = (int) round($timescale / $fps);
    $totalSamples = (int) round($seconds * $fps);
    $mvhd = pack('N', 0).pack('N', 0).pack('N', 0).pack('N', 1000).pack('N', 0).str_repeat("\0", 80);

    $mehd = $withMehd ? mp4Box('mehd', pack('N', 0).pack('N', (int) round($seconds * 1000))) : '';
    $trex = fn (int $track, int $duration) => mp4Box('trex', pack('N', 0).pack('N', $track).pack('N', 1).pack('N', $duration).pack('N', 0).pack('N', 0));
    $mvex = mp4Box('mvex', $mehd.$trex(1, $durationIn === 'trex' ? $perSample : 0).$trex(2, 0));

    $moov = mp4Box('moov',
        mp4Box('mvhd', $mvhd)
        .mp4Track('vide', 'avc1', $width, $height, $timescale, 0, 0, false, 1)
        .($withAudioFragments ? mp4Track('soun', 'mp4a', 0, 0, 44100, 0, 0, false, 2) : '')
        .$mvex
    );

    $perFragment = intdiv($totalSamples, $fragments);
    $body = '';

    for ($i = 0; $i < $fragments; $i++) {
        $count = $i === $fragments - 1 ? $totalSamples - $perFragment * ($fragments - 1) : $perFragment;

        $traf = function (int $track, int $samples, int $duration) use ($durationIn) {
            $tfhdFlags = 0x20000 | ($durationIn === 'tfhd' ? 0x8 : 0);
            $tfhd = pack('N', $tfhdFlags).pack('N', $track).($durationIn === 'tfhd' ? pack('N', $duration) : '');

            if ($durationIn === 'trun') {
                $entries = '';
                for ($n = 0; $n < $samples; $n++) {
                    $entries .= pack('N', $duration).pack('N', 1000); // durasi, ukuran
                }
                $trun = pack('N', 0x301).pack('N', $samples).pack('N', 0).$entries;
            } else {
                $trun = pack('N', 0x1).pack('N', $samples).pack('N', 0);
            }

            return mp4Box('traf', mp4Box('tfhd', $tfhd).mp4Box('trun', $trun));
        };

        $content = mp4Box('mfhd', pack('N', 0).pack('N', $i + 1)).($durationIn === 'none' ? '' : $traf(1, $count, $perSample));

        if ($durationIn === 'none') {
            $content .= mp4Box('traf', mp4Box('tfhd', pack('N', 0x20000).pack('N', 1)));
        }

        if ($withAudioFragments) {
            // jalur audio dengan durasi lain; tidak boleh ikut dihitung sebagai durasi video
            $content .= $traf(2, 5, 1024);
        }

        $body .= mp4Box('moof', $content).mp4Box('mdat', str_repeat("\1", 16));
    }

    return mp4Box('ftyp', 'iso5'.pack('N', 0).'iso5'.'mp41').$moov.$body;
}
