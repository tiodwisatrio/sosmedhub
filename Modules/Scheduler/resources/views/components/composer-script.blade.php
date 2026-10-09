{{--
    Logika Alpine untuk form buat dan ubah jadwal: pilihan format (Feed, Story, Reels), media per
    format, tanggal dan jam, serta data untuk pratinjau. Didaftarkan sekali sebagai komponen
    "postComposer". Pemeriksaan di sini hanya untuk membantu pengguna lebih cepat; server tetap
    yang memutuskan (aturan yang sama ada di FormatMedia).
--}}
<style>
    /* Dua pegangan rentang potong di atas satu lintasan: lintasan tidak menangkap klik, hanya pegangannya. */
    .trim-range {
        position: absolute; inset: 0; width: 100%; height: 100%; margin: 0;
        background: transparent; pointer-events: none; -webkit-appearance: none; appearance: none;
    }
    .trim-range::-webkit-slider-runnable-track { background: transparent; }
    .trim-range::-moz-range-track { background: transparent; }
    .trim-range::-webkit-slider-thumb {
        -webkit-appearance: none; appearance: none; pointer-events: auto; cursor: grab;
        width: 18px; height: 28px; border-radius: 6px; background: #fff; border: 2px solid var(--color-primary, #4f46e5);
        box-shadow: 0 1px 3px rgba(15, 23, 42, .3);
    }
    .trim-range::-moz-range-thumb {
        pointer-events: auto; cursor: grab; width: 14px; height: 24px; border-radius: 6px; background: #fff;
        border: 2px solid var(--color-primary, #4f46e5); box-shadow: 0 1px 3px rgba(15, 23, 42, .3);
    }
    .trim-range:focus-visible::-webkit-slider-thumb { outline: 2px solid var(--color-primary, #4f46e5); outline-offset: 2px; }
</style>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('postComposer', (cfg) => ({
            accountId: cfg.accountId,
            accounts: cfg.accounts,
            fallbackName: cfg.fallbackName,
            keys: ['feed', 'story', 'reel'],
            labels: { feed: 'Feed', story: 'Story', reel: 'Reels' },
            limits: cfg.limits,
            formats: [...cfg.formats],
            locked: [...cfg.locked],
            shareToFeed: cfg.shareToFeed,
            caption: cfg.caption,
            schedDate: cfg.schedDate,
            schedHour: cfg.schedHour,
            schedMinute: cfg.schedMinute,
            maxChars: 2200,
            existing: cfg.existing,
            // File yang dipilih (urutan = urutan kirim ke server), blob pratinjau per file, dan urutan
            // tampil per format: kunci "e:ID" (media tersimpan) atau "n:UID" (file baru).
            files: { feed: [], story: [], reel: [] },
            blobs: {},
            // Rentang potong video Story yang lebih panjang dari batas, per uid file: {duration, start, end}.
            trims: {},
            order: {
                feed: cfg.existing.feed.map((item) => 'e:' + item.id),
                story: cfg.existing.story.map((item) => 'e:' + item.id),
                reel: cfg.existing.reel.map((item) => 'e:' + item.id),
            },
            uidCounter: 0,
            drag: null,
            over: null,
            problems: { feed: '', story: '', reel: '' },
            checking: { feed: false, story: false, reel: false },
            removedIds: [],
            active: cfg.formats[0] || 'feed',
            currentIndex: 0,
            playing: false,
            muted: false,
            progress: 0,

            // ---- Tanggal dan jam -------------------------------------------------------
            pad(n) {
                return String(n).padStart(2, '0');
            },
            parseAt(value) {
                const d = new Date(value.replace('T', ' '));
                return isNaN(d) ? null : d;
            },
            get scheduled_at() {
                return this.schedDate ? `${this.schedDate}T${this.schedHour}:${this.schedMinute}` : '';
            },
            get schedulePreview() {
                const d = this.parseAt(this.scheduled_at || '');
                if (! d) return 'Belum diatur';
                const tgl = d.toLocaleDateString('id-ID', {
                    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
                });
                return `${tgl} · ${this.pad(d.getHours())}:${this.pad(d.getMinutes())} WIB`;
            },

            // ---- Caption ---------------------------------------------------------------
            get remaining() {
                return this.maxChars - (this.caption?.length || 0);
            },
            get counterClass() {
                if (this.remaining < 0) return 'text-danger font-semibold';
                if (this.remaining <= 200) return 'text-warning font-semibold';
                return 'text-slate-400';
            },
            // Story tidak mendukung caption; Feed mewajibkannya.
            get usesCaption() {
                return this.has('feed') || this.has('reel');
            },

            // ---- Format ----------------------------------------------------------------
            // Facebook baru mendukung Feed; memilih akun Facebook mengembalikan format ke Feed.
            init() {
                this.$watch('accountId', () => {
                    if (this.isFacebook) {
                        this.formats = ['feed'];
                        this.setActive('feed');
                    }
                });
            },
            get account() {
                return this.accounts[this.accountId] || null;
            },
            get isFacebook() {
                return this.account?.platform === 'facebook';
            },
            get accountName() {
                return this.account?.name || this.fallbackName;
            },
            get accountAvatar() {
                return this.account?.avatar || null;
            },
            get accountInitial() {
                return (this.accountName || '?').trim().charAt(0).toUpperCase();
            },
            unsupported(format) {
                return this.isFacebook && format !== 'feed';
            },
            has(format) {
                return this.formats.includes(format);
            },
            isLocked(format) {
                return this.locked.includes(format);
            },
            toggle(format) {
                if (this.isLocked(format) || this.unsupported(format)) return;

                if (this.has(format)) {
                    // Minimal satu format tetap terpilih.
                    if (this.formats.length === 1) return;
                    this.formats = this.formats.filter((f) => f !== format);
                } else {
                    this.formats = this.keys.filter((f) => f === format || this.formats.includes(f));
                }

                if (! this.has(this.active)) this.setActive(this.formats[0]);
                this.stopPlayback();
            },
            setActive(format) {
                this.active = format;
                this.currentIndex = 0;
                this.stopPlayback();
            },
            slide(direction) {
                const at = this.formats.indexOf(this.active);
                const next = (at + direction + this.formats.length) % this.formats.length;
                this.setActive(this.formats[next]);
            },

            // ---- Media -----------------------------------------------------------------
            // Media satu format sesuai urutan tampil, tersimpan maupun baru.
            entries(format) {
                return this.order[format].map((key, position) => {
                    if (key.startsWith('e:')) {
                        const id = Number(key.slice(2));
                        const item = this.existing[format].find((i) => i.id === id);
                        return item ? { key, kind: 'existing', id, url: item.url, type: item.type, position } : null;
                    }

                    const uid = key.slice(2);
                    const file = this.files[format].find((f) => String(f._uid) === uid);
                    const blob = this.blobs[uid];
                    return file && blob ? { key, kind: 'new', uid, name: file.name, url: blob.url, type: blob.type, position } : null;
                }).filter(Boolean);
            },
            itemsFor(format) {
                // `trim` hanya ada pada video baru yang dipotong: pratinjau memutar bagian itu saja.
                return this.entries(format).map((entry) => ({ url: entry.url, type: entry.type, trim: entry.kind === 'new' ? (this.trims[entry.uid] || null) : null }));
            },
            get items() {
                return this.itemsFor(this.active);
            },
            get current() {
                return this.items[this.currentIndex] || null;
            },
            // Nama lama yang masih dipakai komponen lain.
            get previewList() {
                return this.items.map((item) => item.url);
            },
            countFor(format) {
                return this.existing[format].length + this.files[format].length;
            },
            capacity(format) {
                return this.limits[format].max;
            },
            step(direction) {
                const total = this.items.length;
                if (total < 2) return;
                this.currentIndex = (this.currentIndex + direction + total) % total;
                this.stopPlayback();
            },

            // ---- Putar video di pratinjau ----------------------------------------------
            togglePlay(event) {
                const video = event.currentTarget.querySelector('video');
                if (! video) return;
                video.muted = this.muted;
                if (video.paused) {
                    video.play().catch(() => { this.playing = false; });
                } else {
                    video.pause();
                }
            },
            toggleMute() {
                this.muted = ! this.muted;
            },
            // Awal pemutaran pratinjau: awal rentang potong bila video dipotong, selain itu detik ke-0,1.
            get previewStart() {
                return this.current?.trim?.start ?? 0.1;
            },
            trackProgress(event) {
                if (! this.playing) return;
                const video = event.target;
                const trim = this.current?.trim;

                if (trim) {
                    // Bagian di luar rentang tidak ditayangkan: ulang dari awal rentang saat melewati akhirnya.
                    if (video.currentTime >= trim.end) video.currentTime = trim.start;
                    this.progress = Math.min(Math.max((video.currentTime - trim.start) / (trim.end - trim.start), 0), 1) * 100;

                    return;
                }

                this.progress = video.duration ? (video.currentTime / video.duration) * 100 : 0;
            },
            // Setelah rentang potong digeser, pratinjau yang sedang berhenti pindah ke awal rentang baru.
            syncPreviewStart() {
                this.$nextTick(() => {
                    this.$root.querySelectorAll('video[data-preview-video]').forEach((video) => {
                        if (video.paused) video.currentTime = Number(video.dataset.start || 0.1);
                    });
                });
            },
            // Menjeda semua video pratinjau (termasuk yang tersembunyi di bingkai lain) agar suaranya
            // tidak terus jalan saat format, item, atau media berganti.
            stopPlayback() {
                this.playing = false;
                this.$root.querySelectorAll('video[data-preview-video]').forEach((video) => {
                    if (! video.paused) video.pause();
                    video.currentTime = Number(video.dataset.start || 0.1);
                });
                this.progress = 0;
            },
            summaryFor(format) {
                const items = this.itemsFor(format);
                if (items.length === 0) return 'Belum ada media';
                const videos = items.filter((i) => i.type === 'video').length;
                const photos = items.length - videos;
                const parts = [];
                if (photos) parts.push(`${photos} foto`);
                if (videos) parts.push(`${videos} video`);
                return parts.join(' + ');
            },
            get photoCountLabel() {
                return this.summaryFor(this.active);
            },

            accepts(format) {
                if (format === 'feed') return 'image/jpeg';
                if (format === 'story') return 'image/jpeg,video/mp4,video/quicktime';
                return 'video/mp4,video/quicktime';
            },
            problemFor(format, file, trimmable = false) {
                const isPhoto = file.type === 'image/jpeg';
                const isVideo = ['video/mp4', 'video/quicktime'].includes(file.type);
                const mb = file.size / 1048576;

                if (format === 'feed' && ! isPhoto) return 'Feed hanya menerima foto JPEG.';
                if (format === 'reel' && ! isVideo) return 'Reels hanya menerima video MP4 atau MOV.';
                if (! isPhoto && ! isVideo) return 'Format file tidak didukung. Gunakan JPEG, MP4, atau MOV.';
                if (isPhoto && mb > this.limits.photoMb) return `Ukuran foto maksimal ${this.limits.photoMb} MB.`;

                if (isVideo) {
                    const max = trimmable ? this.limits.trimSourceMb : this.limits[format].videoMb;
                    if (mb > max) return `Ukuran video ${mb.toFixed(1)} MB melebihi batas ${max} MB untuk ${this.labels[format]}.`;
                }

                return null;
            },
            // Durasi video dibaca dari browser. Bila browser tidak bisa membacanya, server yang memutuskan.
            probeDuration(file) {
                return new Promise((resolve) => {
                    const video = document.createElement('video');
                    const url = URL.createObjectURL(file);
                    const finish = (value) => {
                        URL.revokeObjectURL(url);
                        resolve(value);
                    };
                    video.preload = 'metadata';
                    video.onloadedmetadata = () => finish(Number.isFinite(video.duration) ? video.duration : null);
                    video.onerror = () => finish(null);
                    setTimeout(() => finish(null), 8000);
                    video.src = url;
                });
            },
            formatSeconds(seconds) {
                const whole = Math.round(seconds);
                if (whole < 60) return `${whole} detik`;
                const m = Math.floor(whole / 60);
                const s = whole % 60;
                return s ? `${m} menit ${s} detik` : `${m} menit`;
            },
            async addFiles(format, event) {
                const incoming = Array.from(event.target.files || []);
                this.problems[format] = '';
                this.checking[format] = true;
                const room = this.capacity(format) - this.countFor(format);
                const accepted = [];
                const messages = [];

                for (const file of incoming) {
                    if (accepted.length >= room) {
                        messages.push(format === 'reel'
                            ? 'Reels hanya bisa berisi satu video. Hapus video lama dulu.'
                            : `${this.labels[format]} maksimal ${this.capacity(format)} item.`);
                        break;
                    }

                    const isVideo = file.type.startsWith('video/');
                    const duration = isVideo ? await this.probeDuration(file) : null;
                    const max = this.limits[format].videoSeconds;
                    // Story yang kepanjangan tidak ditolak: penggunanya memilih bagian yang ditayangkan.
                    const trimmable = format === 'story' && this.limits.trimEnabled && duration !== null && duration > max;

                    let problem = this.problemFor(format, file, trimmable);

                    if (! problem && isVideo && ! trimmable) {
                        if (duration !== null && duration > max) {
                            problem = `Durasi video ${this.formatSeconds(duration)} melebihi batas ${this.formatSeconds(max)} untuk ${this.labels[format]}.`;
                        } else if (duration !== null && duration < this.limits.videoMinSeconds) {
                            problem = `Video terlalu pendek. Minimal ${this.limits.videoMinSeconds} detik.`;
                        }
                    }

                    if (! problem && trimmable) file._trimDuration = duration;

                    problem ? messages.push(`${file.name}: ${problem}`) : accepted.push(file);
                }

                accepted.forEach((file) => {
                    file._uid = ++this.uidCounter;
                    this.blobs[file._uid] = { url: URL.createObjectURL(file), type: file.type.startsWith('video/') ? 'video' : 'image' };
                    if (file._trimDuration) {
                        this.trims[file._uid] = { duration: file._trimDuration, start: 0, end: Math.min(this.limits.story.videoSeconds, file._trimDuration) };
                    }
                    this.order[format].push('n:' + file._uid);
                });
                this.files[format] = [...this.files[format], ...accepted];
                this.syncInput(format);
                this.problems[format] = messages.join(' ');
                this.checking[format] = false;
                this.currentIndex = Math.min(this.currentIndex, Math.max(this.items.length - 1, 0));
            },
            // ---- Potong video Story ----------------------------------------------------
            // Video yang perlu dipotong, berurutan seperti file di input (nomor urut dikirim ke server).
            get trimEntries() {
                return this.files.story
                    .map((file, index) => ({ file, index, uid: file._uid, trim: this.trims[file._uid] }))
                    .filter((entry) => entry.trim);
            },
            onTrimStart(entry, event) {
                const t = entry.trim;
                const max = this.limits.story.videoSeconds;
                const min = this.limits.videoMinSeconds;
                t.start = Math.min(Number(event.target.value), t.duration - min);
                if (t.end - t.start > max) t.end = t.start + max;
                if (t.end - t.start < min) t.end = Math.min(t.start + min, t.duration);
                this.seekTrim(entry, t.start);
                this.syncPreviewStart();
            },
            onTrimEnd(entry, event) {
                const t = entry.trim;
                const max = this.limits.story.videoSeconds;
                const min = this.limits.videoMinSeconds;
                t.end = Math.max(Number(event.target.value), min);
                if (t.end - t.start > max) t.start = t.end - max;
                if (t.end - t.start < min) t.start = Math.max(t.end - min, 0);
                this.seekTrim(entry, Math.max(t.end - 2, t.start));
                this.syncPreviewStart();
            },
            seekTrim(entry, seconds) {
                const video = document.getElementById('trim-video-' + entry.uid);
                if (video) video.currentTime = seconds;
            },
            // Pratinjau hanya memutar bagian yang dipilih: lompat kembali ke awal saat melewati akhir.
            loopTrim(entry, event) {
                const video = event.target;
                if (video.currentTime >= entry.trim.end || video.currentTime < entry.trim.start - 0.3) {
                    video.currentTime = entry.trim.start;
                }
            },
            clock(seconds) {
                const whole = Math.max(Math.round(seconds), 0);
                return Math.floor(whole / 60) + ':' + String(whole % 60).padStart(2, '0');
            },
            trimSummary(trim) {
                return `${this.clock(trim.start)} – ${this.clock(trim.end)} (${this.formatSeconds(trim.end - trim.start)})`;
            },

            // Urutan file di input mengikuti urutan tampil, supaya nomor "n:N" dari server cocok.
            syncInput(format) {
                const rank = (file) => this.order[format].indexOf('n:' + file._uid);
                this.files[format] = [...this.files[format]].sort((a, b) => rank(a) - rank(b));

                const transfer = new DataTransfer();
                this.files[format].forEach((file) => transfer.items.add(file));
                const input = this.$root.querySelector(`[data-media-input="${format}"]`);
                if (input) input.files = transfer.files;
            },
            // Token urutan yang dikirim ke server: "e:ID" apa adanya, file baru diberi nomor menurut urutan kirim.
            orderTokens(format) {
                let next = 0;
                return this.order[format].map((key) => (key.startsWith('e:') ? key : 'n:' + next++));
            },
            removeNew(format, key) {
                const uid = key.slice(2);
                if (this.blobs[uid]) URL.revokeObjectURL(this.blobs[uid].url);
                delete this.blobs[uid];
                delete this.trims[uid];
                this.files[format] = this.files[format].filter((file) => String(file._uid) !== uid);
                this.order[format] = this.order[format].filter((k) => k !== key);
                this.syncInput(format);
                this.problems[format] = '';
                this.clampIndex();
            },
            removeExisting(format, id) {
                this.removedIds.push(id);
                this.existing[format] = this.existing[format].filter((item) => item.id !== id);
                this.order[format] = this.order[format].filter((k) => k !== 'e:' + id);
                this.problems[format] = '';
                this.clampIndex();
            },

            // ---- Urutan: seret-lepas dan tombol geser -----------------------------------
            canReorder(format) {
                return ! this.isLocked(format) && this.order[format].length > 1;
            },
            dragStart(format, key, event) {
                if (! this.canReorder(format)) {
                    event.preventDefault();
                    return;
                }

                this.drag = { format, key };
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', key);
            },
            dragOver(format, key, event) {
                if (! this.drag || this.drag.format !== format) return;
                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
                this.over = key;
            },
            drop(format, key) {
                if (this.drag && this.drag.format === format) this.moveTo(format, this.drag.key, key);
                this.endDrag();
            },
            endDrag() {
                this.drag = null;
                this.over = null;
            },
            // Media yang dipindah menempati posisi target; yang lain bergeser.
            moveTo(format, fromKey, toKey) {
                if (fromKey === toKey || this.isLocked(format)) return;
                const order = [...this.order[format]];
                const from = order.indexOf(fromKey);
                const to = order.indexOf(toKey);
                if (from < 0 || to < 0) return;

                order.splice(from, 1);
                order.splice(to, 0, fromKey);
                this.order[format] = order;
                this.syncInput(format);
                this.stopPlayback();

                if (this.active === format) this.currentIndex = to;
            },
            shift(format, key, delta) {
                const order = this.order[format];
                const target = order[order.indexOf(key) + delta];
                if (target) this.moveTo(format, key, target);
            },
            clampIndex() {
                this.currentIndex = Math.min(this.currentIndex, Math.max(this.items.length - 1, 0));
                this.stopPlayback();
            },
        }));
    });
</script>
