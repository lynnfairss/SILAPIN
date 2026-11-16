/* Mesin OCR kartu identitas SILAPIN.
   Dipakai form peminjaman dan halaman uji /ocr-lab.php supaya keduanya
   mengukur pipeline yang sama. */
(function (global) {
    'use strict';

    // Salinan lokal harus dicari relatif terhadap berkas ini, bukan terhadap
    // root domain: kalau aplikasi dipasang di subfolder (htdocs/silapin/public)
    // path root-absolute jatuh ke http://host/vendor/... yang 404, dan setiap
    // OCR ikut mengunduh mesin dari CDN.
    const VENDOR = (function () {
        const self = document.currentScript && document.currentScript.src;
        if (!self) return '/vendor/tesseract';
        return new URL('../vendor/tesseract', self).href.replace(/\/$/, '');
    })();
    const CDN = 'https://cdn.jsdelivr.net/npm/tesseract.js@5';

    // Lebar kerja pemrosesan. Di bawah 1500px tinggi huruf KTP (~2mm) jatuh
    // di bawah ~28px dan LSTM mulai menebak; di atas 2400px biaya CPU naik
    // tanpa tambahan informasi optik.
    const WORK_W_MIN = 1500;
    const WORK_W_MAX = 2400;

    let scriptPromise = null;
    let worker = null;
    let workerBusy = false;

    function loadScript() {
        if (typeof global.Tesseract !== 'undefined' && global.Tesseract.createWorker) {
            return Promise.resolve();
        }
        if (scriptPromise) return scriptPromise;

        scriptPromise = inject(VENDOR + '/tesseract.min.js')
            .catch(function () {
                if (typeof global.Tesseract !== 'undefined' && global.Tesseract.createWorker) return null;
                return inject(CDN + '/dist/tesseract.min.js');
            })
            .then(function () {
                if (!global.Tesseract || !global.Tesseract.createWorker) {
                    throw new Error('mesin OCR tidak dapat dimuat');
                }
            })
            .catch(function (err) {
                scriptPromise = null;
                throw err;
            });

        return scriptPromise;
    }

    function inject(src) {
        return new Promise(function (resolve, reject) {
            const s = document.createElement('script');
            s.src = src;
            s.async = true;
            s.onload = resolve;
            s.onerror = function () { reject(new Error('gagal memuat ' + src)); };
            document.head.appendChild(s);
        });
    }

    // Berkas vendor lokal isinya identik dengan CDN (sudah dicocokkan per
    // md5), jadi dipakai lebih dulu: HP dengan sinyal lemah tetap bisa OCR.
    // Path harus mutlak — worker menaruhnya di importScripts() yang tidak
    // mengenal path root-relative.
    function abs(p) { return new URL(p, global.location.href).href; }

    function workerOptions(base) {
        const o = {};
        for (const k in base) o[k] = /^https?:/.test(base[k]) ? base[k] : abs(base[k]);
        return o;
    }

    async function getWorker(onProgress) {
        if (worker) return worker;
        await loadScript();

        const bases = [
            workerOptions({ workerPath: VENDOR + '/worker.min.js', corePath: VENDOR, langPath: VENDOR }),
            workerOptions({ workerPath: CDN + '/dist/worker.min.js' }),
        ];

        let lastError = null;
        for (const opt of bases) {
            try {
                const w = await global.Tesseract.createWorker('ind', 1, Object.assign({ logger: onProgress || null }, opt));
                worker = w;
                return worker;
            } catch (err) {
                lastError = err;
            }
        }
        throw lastError || new Error('worker OCR gagal dibuat');
    }

    /* ---------- PRA-PROSES GAMBAR ---------- */

    function readGray(ctx, w, h) {
        const data = ctx.getImageData(0, 0, w, h).data;
        const gray = new Float32Array(w * h);
        for (let i = 0, p = 0; i < data.length; i += 4, p++) {
            gray[p] = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
        }
        return gray;
    }

    function integral(gray, w, h) {
        const I = new Float64Array((w + 1) * (h + 1));
        for (let y = 0; y < h; y++) {
            let rowSum = 0;
            const base = (y + 1) * (w + 1);
            const prev = y * (w + 1);
            for (let x = 0; x < w; x++) {
                rowSum += gray[y * w + x];
                I[base + x + 1] = I[prev + x + 1] + rowSum;
            }
        }
        return I;
    }

    function boxSum(I, w, x0, y0, x1, y1) {
        const s = w + 1;
        return I[(y1 + 1) * s + x1 + 1] - I[y0 * s + x1 + 1] - I[(y1 + 1) * s + x0] + I[y0 * s + x0];
    }

    function boxBlur(gray, w, h, r) {
        const I = integral(gray, w, h);
        const out = new Float32Array(gray.length);
        for (let y = 0; y < h; y++) {
            const y0 = Math.max(0, y - r), y1 = Math.min(h - 1, y + r);
            for (let x = 0; x < w; x++) {
                const x0 = Math.max(0, x - r), x1 = Math.min(w - 1, x + r);
                out[y * w + x] = boxSum(I, w, x0, y0, x1, y1) / ((x1 - x0 + 1) * (y1 - y0 + 1));
            }
        }
        return out;
    }

    // Ratakan pencahayaan: kartu dibagi citra latar blur-nya sendiri, lalu
    // dikembalikan ke rentang asal. Ini yang menghapus bayangan jari dan
    // lampu yang membuat binarisasi global gagal total. `mix` menjaga agar
    // kontras alami kartu tidak ikut hilang bersama bayangannya.
    function flattenLighting(gray, w, h, mix) {
        const t = mix === undefined ? 1 : mix;
        const r = Math.max(10, Math.round(Math.min(w, h) / 7));
        const bg = boxBlur(boxBlur(gray, w, h, r), w, h, r);
        let bgMean = 0;
        for (let i = 0; i < bg.length; i++) bgMean += bg[i];
        bgMean /= bg.length;

        const out = new Float32Array(gray.length);
        for (let i = 0; i < gray.length; i++) {
            const denom = Math.max(24, bg[i]);
            const corrected = Math.max(0, Math.min(255, gray[i] * (bgMean / denom)));
            out[i] = gray[i] * (1 - t) + corrected * t;
        }
        return out;
    }

    function stretchContrast(gray, lowCut) {
        const cut = lowCut === undefined ? 0.02 : lowCut;
        const hist = new Uint32Array(256);
        for (let i = 0; i < gray.length; i++) hist[Math.max(0, Math.min(255, gray[i] | 0))]++;

        const total = gray.length;
        const target = total * cut;
        let lo = 0, hi = 255, acc = 0;
        for (let v = 0; v < 256; v++) { acc += hist[v]; if (acc >= target) { lo = v; break; } }
        acc = 0;
        for (let v = 255; v >= 0; v--) { acc += hist[v]; if (acc >= target) { hi = v; break; } }
        if (hi - lo < 24) { lo = 0; hi = 255; }

        const scale = 255 / Math.max(1, hi - lo);
        for (let i = 0; i < gray.length; i++) {
            gray[i] = Math.max(0, Math.min(255, (gray[i] - lo) * scale));
        }
        return gray;
    }

    // Penajaman lokal memakai selisih terhadap blur-nya sendiri. Radius 2 pada
    // lebar kerja ini menyentuh stroke huruf KTP tanpa memunculkan halo.
    function unsharpMask(gray, w, h, amount) {
        const blurred = boxBlur(gray, w, h, 2);
        const out = new Float32Array(gray.length);
        for (let i = 0; i < gray.length; i++) {
            const v = gray[i];
            out[i] = Math.max(0, Math.min(255, v + amount * (v - blurred[i])));
        }
        return out;
    }

    // Binarisasi Sauvola dengan jendela mengikuti tinggi huruf. Jendela tetap
    // 25px salah di kedua arah: terlalu lebar di kartu kecil (teks ikut rata),
    // terlalu sempit di kartu besar (pola guilloche jadi teks).
    function sauvola(gray, w, h, win, k) {
        const R = 128;
        const I = integral(gray, w, h);
        const sq = new Float32Array(gray.length);
        for (let i = 0; i < gray.length; i++) sq[i] = gray[i] * gray[i];
        const I2 = integral(sq, w, h);

        const out = new Uint8Array(w * h);
        const r = Math.floor(win / 2);
        for (let y = 0; y < h; y++) {
            const y0 = Math.max(0, y - r), y1 = Math.min(h - 1, y + r);
            for (let x = 0; x < w; x++) {
                const x0 = Math.max(0, x - r), x1 = Math.min(w - 1, x + r);
                const area = (x1 - x0 + 1) * (y1 - y0 + 1);
                const mean = boxSum(I, w, x0, y0, x1, y1) / area;
                const variance = Math.max(0, boxSum(I2, w, x0, y0, x1, y1) / area - mean * mean);
                const threshold = mean * (1 + k * (Math.sqrt(variance) / R - 1));
                out[y * w + x] = gray[y * w + x] < threshold ? 0 : 255;
            }
        }
        return out;
    }

    // Tinggi baris teks diperkirakan dari periode proyeksi horizontal: jarak
    // antar lembah dominan pada profil baris gelap.
    function estimateLineHeight(bin, w, h) {
        const prof = new Float32Array(h);
        for (let y = 0; y < h; y++) {
            let dark = 0;
            for (let x = 0; x < w; x++) if (bin[y * w + x] === 0) dark++;
            prof[y] = dark;
        }
        let mean = 0;
        for (let y = 0; y < h; y++) mean += prof[y];
        mean /= Math.max(1, h);

        const valleys = [];
        for (let y = 2; y < h - 2; y++) {
            if (prof[y] <= mean * 0.25 && prof[y] <= prof[y - 1] && prof[y] <= prof[y + 1]) {
                if (!valleys.length || y - valleys[valleys.length - 1] >= 6) valleys.push(y);
            }
        }
        if (valleys.length < 3) return 0;

        const gaps = [];
        for (let i = 1; i < valleys.length; i++) {
            const g = valleys[i] - valleys[i - 1];
            if (g >= 14 && g <= 120) gaps.push(g);
        }
        if (gaps.length < 2) return 0;
        gaps.sort(function (a, b) { return a - b; });
        return gaps[Math.floor(gaps.length / 2)];
    }

    function estimateSkew(bin, w, h) {
        const step = Math.max(1, Math.round(w / 700));
        const xs = [];
        const ys = [];
        for (let y = 0; y < h; y += step) {
            for (let x = 0; x < w; x += step) {
                if (bin[y * w + x] === 0) { xs.push(x); ys.push(y); }
            }
        }
        if (xs.length < 60) return 0;

        let best = 0, bestScore = -1;
        for (let deg = -6; deg <= 6; deg += 0.5) {
            const rad = deg * Math.PI / 180;
            const cos = Math.cos(rad), sin = Math.sin(rad);
            const bins = new Float64Array(h + 2 * w);
            const offset = w;
            for (let i = 0; i < xs.length; i++) {
                const p = Math.round(ys[i] * cos - xs[i] * sin) + offset;
                if (p >= 0 && p < bins.length) bins[p]++;
            }
            let mean = 0;
            for (let i = 0; i < bins.length; i++) mean += bins[i];
            mean /= bins.length;
            let v = 0;
            for (let i = 0; i < bins.length; i++) {
                const d = bins[i] - mean;
                v += d * d;
            }
            if (v > bestScore) { bestScore = v; best = deg; }
        }
        return best;
    }

    function rotateCanvas(canvas, deg) {
        const rad = deg * Math.PI / 180;
        const w = canvas.width, h = canvas.height;
        const out = document.createElement('canvas');
        out.width = w;
        out.height = h;
        const ctx = out.getContext('2d', { willReadFrequently: true });
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, w, h);
        ctx.translate(w / 2, h / 2);
        ctx.rotate(rad);
        ctx.drawImage(canvas, -w / 2, -h / 2);
        return out;
    }

    function despeckle(bin, w, h) {
        const copy = bin.slice();
        for (let y = 1; y < h - 1; y++) {
            for (let x = 1; x < w - 1; x++) {
                const i = y * w + x;
                const v = copy[i];
                let same = 0;
                for (let dy = -1; dy <= 1; dy++) {
                    for (let dx = -1; dx <= 1; dx++) {
                        if (dx === 0 && dy === 0) continue;
                        if (copy[i + dy * w + dx] === v) same++;
                    }
                }
                if (same <= 2) bin[i] = v === 0 ? 255 : 0;
            }
        }
    }

    function writeBinary(ctx, bin, w, h) {
        const img = ctx.createImageData(w, h);
        const d = img.data;
        for (let i = 0, p = 0; p < bin.length; i += 4, p++) {
            const v = bin[p];
            d[i] = d[i + 1] = d[i + 2] = v;
            d[i + 3] = 255;
        }
        ctx.putImageData(img, 0, 0);
    }

    function writeGray(ctx, gray, w, h) {
        const img = ctx.createImageData(w, h);
        const d = img.data;
        for (let i = 0, p = 0; p < gray.length; i += 4, p++) {
            const v = gray[p] | 0;
            d[i] = d[i + 1] = d[i + 2] = v;
            d[i + 3] = 255;
        }
        ctx.putImageData(img, 0, 0);
    }

    function sourceSize(source) {
        return {
            w: source.naturalWidth || source.videoWidth || source.width || 0,
            h: source.naturalHeight || source.videoHeight || source.height || 0,
        };
    }

    // flatten mati secara default: pada korpus uji, perataan pencahayaan menaikkan
    // nama/ttl sedikit tetapi menghapus NIK+alamat pada tangkapan kamera resolusi
    // rendah (kasus paling umum di ponsel), jadi rugi bersih.
    const PREP_DEFAULT = { flatten: false, unsharp: 0.8, cut: 0.03, k: 0.18 };

    // Kembalikan dua varian citra kerja: biner tajam dan kelabu terhitung rata.
    // LSTM Tesseract lebih sering benar pada kelabu, sedangkan biner menang di
    // kartu kecil/rendah resolusi; pemanggil memakai keduanya bergantian.
    function prepare(source, options) {
        const cfg = Object.assign({}, PREP_DEFAULT, options || {});
        const size = sourceSize(source);
        if (!size.w || !size.h) return null;

        const w = Math.min(Math.max(size.w, cfg.workMin || WORK_W_MIN), cfg.workMax || WORK_W_MAX);
        const h = Math.max(1, Math.round(size.h * (w / size.w)));

        let canvas = document.createElement('canvas');
        canvas.width = w;
        canvas.height = h;
        let ctx = canvas.getContext('2d', { willReadFrequently: true });
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, w, h);
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        ctx.drawImage(source, 0, 0, w, h);

        function pipeline(cv, c) {
            let gray = readGray(c, cv.width, cv.height);
            if (cfg.flatten) gray = flattenLighting(gray, cv.width, cv.height, cfg.flatten === true ? 1 : cfg.flatten);
            gray = stretchContrast(gray, cfg.cut);
            gray = unsharpMask(gray, cv.width, cv.height, cfg.unsharp);
            const probe = sauvola(gray, cv.width, cv.height, 25, 0.2);
            const lineH = estimateLineHeight(probe, cv.width, cv.height);
            const win = lineH ? Math.max(15, Math.min(51, Math.round(lineH * 0.6) | 1)) : 25;
            const bin = sauvola(gray, cv.width, cv.height, win, cfg.k);
            return { gray: gray, bin: bin };
        }

        let stage = pipeline(canvas, ctx);

        const skew = estimateSkew(stage.bin, w, h);
        if (Math.abs(skew) >= 0.5) {
            canvas = rotateCanvas(canvas, -skew);
            ctx = canvas.getContext('2d', { willReadFrequently: true });
            stage = pipeline(canvas, ctx);
        }

        const bw = document.createElement('canvas');
        bw.width = canvas.width;
        bw.height = canvas.height;
        const bwCtx = bw.getContext('2d', { willReadFrequently: true });

        despeckle(stage.bin, canvas.width, canvas.height);
        writeBinary(bwCtx, stage.bin, canvas.width, canvas.height);

        const gs = document.createElement('canvas');
        gs.width = canvas.width;
        gs.height = canvas.height;
        writeGray(gs.getContext('2d', { willReadFrequently: true }), stage.gray, gs.width, gs.height);

        return { binary: bw, gray: gs, width: canvas.width, height: canvas.height, skew: skew };
    }

    // Potong pita tempat NIK dicetak (kiri-bawah kartu) untuk pass terakhir.
    function nikBand(prepared) {
        const out = document.createElement('canvas');
        const pad = Math.round(prepared.width * 0.01);
        out.width = Math.round(prepared.width * 0.62) + pad;
        out.height = Math.round(prepared.height * 0.30) + pad;
        out.getContext('2d').drawImage(prepared.binary, 0, Math.round(prepared.height * 0.66), out.width, out.height, 0, 0, out.width, out.height);
        return out;
    }

    /* ---------- PENGURAIAN TEKS ---------- */

    const DIGIT_FIX = {
        O: '0', o: '0', Q: '0', D: '0',
        I: '1', l: '1', i: '1', t: '1',
        Z: '2', z: '2',
        S: '5', s: '5',
        G: '6', b: '6',
        B: '8',
        g: '9', q: '9',
    };

    // Perbaiki token yang jelas-jelas nomor tapi ter-OCR bercampur huruf.
    function denoiseDigits(token) {
        let digits = '';
        for (const ch of token) {
            if (ch >= '0' && ch <= '9') digits += ch;
            else if (DIGIT_FIX[ch]) digits += DIGIT_FIX[ch];
            else if (/[ \-./:,]/.test(ch)) continue;
            else return '';
        }
        return digits;
    }

    function findNik(text, lines) {
        const candidates = [];

        for (let i = 0; i < lines.length; i++) {
            const line = lines[i];
            const compact = line.replace(/\s+/g, '');
            let run = compact.match(/[0-9]{16}/);
            let value = run ? run[0] : '';

            if (!value) {
                // Belum ada deret 16 digit murni: perbaiki karakter mirip digit
                // lebih dulu, baru cari lagi.
                const repaired = compact.split('').map(function (ch) {
                    return (ch >= '0' && ch <= '9') || DIGIT_FIX[ch] ? (DIGIT_FIX[ch] || ch) : ' ';
                }).join('');
                for (const m of repaired.match(/[0-9]{16,17}/g) || []) {
                    value = m.slice(0, 16);
                    break;
                }
                // Nomor yang tercetak dengan spasi antar blok (3502 0112 0890 0001).
                if (!value) {
                    const joined = (compact.match(/[0-9]{4}[ .-]?[0-9]{4}[ .-]?[0-9]{4}[ .-]?[0-9]{4}/) || [])[0];
                    if (joined) value = joined.replace(/[^0-9]/g, '');
                }
            }

            if (value.length === 16) {
                const near = /nik/i.test(line) || /nik/i.test(lines[i - 1] || '') || /nomor induk/i.test(line);
                candidates.push({ value: value, labelBonus: near ? 1000 : 0 });
            }
        }

        // Pita digit berdiri sendiri (hasil pass whitelist) ikut dinilai.
        for (const raw of text.match(/\b[0-9OQlSiIzZsSgGqQBD]{16,18}\b/g) || []) {
            const d = denoiseDigits(raw);
            if (d.length === 16) candidates.push({ value: d, labelBonus: 0 });
        }

        if (!candidates.length) return '';

        // 6 digit pertama NIK adalah kode wilayah administrasi dan selalu
        // angka; kalau kandidat lain lolos, prioritaskan yang cocok pola itu.
        candidates.sort(function (a, b) {
            return (b.labelBonus + validness(b.value)) - (a.labelBonus + validness(a.value));
        });
        return candidates[0].value;
    }

    // Daftar kode wilayah provinsi pada dua digit pertama NIK (Permendagri).
    // 20, 22-30, 40-50, 54-60, 66-70, 77-80, 83-90 tidak terpakai, jadi angka
    // di luar daftar ini hampir pasti hasil salah baca OCR.
    const PROVINSI_NIK = ['11', '12', '13', '14', '15', '16', '17', '18', '19', '21',
        '31', '32', '33', '34', '35', '36', '51', '52', '53', '61', '62', '63', '64', '65',
        '71', '72', '73', '74', '75', '76', '81', '82', '91', '92', '93', '94', '95', '96', '97', '98'];

    // Enam digit pertama NIK adalah kode wilayah (provinsi-kabupaten-kecamatan),
    // jadi hanya dua digit pertama yang punya rentang ketat. Tanggal lahir
    // tercetak di digit 7-12 sebagai ddmmyy, hari perempuan ditambah 40.
    function validness(nik) {
        let s = 0;
        if (PROVINSI_NIK.indexOf(nik.slice(0, 2)) >= 0) s += 4;
        const hari = +nik.slice(6, 8);
        const bulan = +nik.slice(8, 10);
        if ((hari >= 1 && hari <= 31) || (hari >= 41 && hari <= 71)) s += 2;
        if (bulan >= 1 && bulan <= 12) s += 2;
        return s;
    }

    // Tanggal lahir bisa direkonstruksi dari NIK ketika baris TTL tidak
    // terbaca. Tahun abad ditebak dari umur: angka yang lebih besar dari tahun
    // sekarang berarti lahir di 19xx.
    function nikBirthdate(nik) {
        if (!nik || nik.length !== 16) return '';
        let hari = +nik.slice(6, 8);
        const bulan = +nik.slice(8, 10);
        const yy = +nik.slice(10, 12);
        if (hari > 40) hari -= 40;
        if (hari < 1 || hari > 31 || bulan < 1 || bulan > 12) return '';
        const abad = yy > (new Date().getFullYear() % 100) ? 1900 : 2000;
        return pad2(hari) + '-' + pad2(bulan) + '-' + (abad + yy);
    }

    function cleanValue(s) {
        return s.replace(/[ \t]+/g, ' ').replace(/\s*\n\s*/g, ' ').trim();
    }

    const STOP_RE = /^(tempat\s*\/?\s*tgl|tgl\s*lahir|jenis\s*kelamin|golongan\s*darah|agama|status\s*perk|pekerjaan|kewarganegaraan|berlaku\s*sampai|nik|nomor\s*nik|provinsi|nama|alamat|rt\s*[\/\\]\s*rw|rt\/rw|kel\s*[.\/]\s*desa|kecamatan)/i;

    // Baris label KTP bisa terpotong ("NAMA :" di baris terpisah) maupun
    // tergabung ("NAMA: BUDI SANTOSO AGAMA ISLAM"), jadi nilai diambil dari
    // sisa baris lalu dipangkas pada label berikutnya.
    function grabValue(lines, labelRe) {
        for (let i = 0; i < lines.length; i++) {
            const m = lines[i].match(labelRe);
            if (!m) continue;

            let rest = (m[1] || '').trim();
            const stop = rest.match(STOP_RE);
            if (stop && stop.index > 0) rest = rest.slice(0, stop.index).trim();

            if (rest.replace(/[^a-z0-9]/gi, '').length >= 3) return cleanValue(rest);

            const val = [];
            for (let j = i + 1; j < lines.length; j++) {
                const nxt = lines[j].replace(/\s+/g, '');
                if (STOP_RE.test(nxt)) break;
                if (!nxt) continue;
                val.push(lines[j]);
                if (val.length >= 3) break;
            }
            const joined = cleanValue(val.join(' '));
            if (joined.replace(/[^a-z0-9]/gi, '').length >= 2) return joined;
        }
        return '';
    }

    function normalizeDate(s) {
        // KTP lama menulis 12-08-1990, KTP-el sering 08-12-1990, ada pula
        // "12 AGUSTUS 1990". Bentuk yang disimpan cukup dd-mm-yyyy.
        const bulan = {
            januari: 1, februari: 2, maret: 3, april: 4, mei: 5, juni: 6,
            juli: 7, agustus: 8, september: 9, oktober: 10, nopember: 11, november: 11, desember: 12,
        };
        let m = s.match(/\b(\d{1,2})\s*[-\/.]\s*(\d{1,2})\s*[-\/.]\s*(\d{4})\b/);
        if (m) {
            let a = +m[1], b = +m[2], y = +m[3];
            if (a > 31 && b <= 31) { const t = a; a = b; b = t; }
            a = Math.min(31, a); b = Math.max(1, Math.min(12, b));
            return pad2(a) + '-' + pad2(b) + '-' + y;
        }
        m = s.match(/\b(\d{1,2})\s+([a-z]+)\s+(\d{4})\b/i);
        if (m && bulan[m[2].toLowerCase()]) {
            return pad2(+m[1]) + '-' + pad2(bulan[m[2].toLowerCase()]) + '-' + m[3];
        }
        return '';
    }

    function pad2(n) { return (n < 10 ? '0' : '') + n; }

    // Baris "Tempat / Tgl Lahir" adalah yang paling sering rusak: kartunya
    // miring, labelnya tipis, dan OCR mengubah "Tempat/Tgl" menjadi
    // "TempaiTgi", "Tempai/Tg", "Tempat/Tgi". Mencocokkan label saja tidak
    // cukup, jadi yang dipakai dua jejak lain yang tercetak di baris yang
    // sama: tanggal dd-mm-yyyy dan nilai tempat yang dicetak kapital semua
    // sementara label dicetak besar-kecil campur.
    const TTL_WORDS = ['tempat', 'tgl', 'lahir'];

    function editDistance(a, b) {
        const prev = [];
        for (let j = 0; j <= b.length; j++) prev[j] = j;
        for (let i = 1; i <= a.length; i++) {
            let last = prev[0];
            prev[0] = i;
            for (let j = 1; j <= b.length; j++) {
                const tmp = prev[j];
                prev[j] = Math.min(prev[j] + 1, prev[j - 1] + 1, last + (a[i - 1] === b[j - 1] ? 0 : 1));
                last = tmp;
            }
        }
        return prev[b.length];
    }

    function looksLikeTtlLabel(s) {
        const letters = (s || '').toLowerCase().replace(/[^a-z]/g, ' ');
        for (const word of TTL_WORDS) {
            if (letters.indexOf(word) >= 0) return true;
        }
        for (const token of letters.split(/\s+/)) {
            if (token.length < 3) continue;
            for (const word of TTL_WORDS) {
                if (Math.abs(token.length - word.length) <= 2 && editDistance(token, word) <= 2) return true;
            }
        }
        return false;
    }

    // Ambil bagian kapital di ujung depan-tanggal: itulah nilai tempat.
    function placeBeforeDate(head) {
        const tokens = (head || '').trim().split(/[\s,]+/).filter(Boolean);
        let end = tokens.length;
        while (end > 0) {
            const letters = tokens[end - 1].replace(/[^A-Za-z]/g, '');
            if (letters.length < 2 || letters !== letters.toUpperCase() || /[a-z]/.test(tokens[end - 1])) break;
            end--;
        }
        const run = tokens.slice(end);
        // Label yang ter-OCR kapital semua ("TEMPAT/TGL LAHIR") ikut terbawa;
        // buang dari depan, tapi sisakan minimal satu token — nama tempat bisa
        // saja mirip kata label ("TEGAL" vs "TGL", "LAHAT" vs "LAHIR").
        while (run.length > 1 && looksLikeTtlLabel(run[0])) run.shift();
        const place = run.join(' ').replace(/[^A-Za-z0-9 .'-]/g, '').trim();
        return place.replace(/[\s,.-]+$/, '');
    }

    function ttlFromLine(line) {
        const dm = line.match(/(?:^|[^\d])(\d{1,2}\s*[-\/.]\s*\d{1,2}\s*[-\/.]\s*\d{4})/);
        if (!dm) return null;
        const date = normalizeDate(dm[1]);
        if (!date) return null;
        const head = line.slice(0, dm.index + dm[0].length - dm[1].length);
        const tempat = placeBeforeDate(head);
        // Tempat lahir tidak pernah memakai digit; kalau isinya angka, yang
        // terbaca kemungkinan besar baris NIK atau kode pos.
        if (tempat.length < 3 || /\d/.test(tempat)) return null;
        return { tempat: tempat, tanggalLahir: date, ttl: cleanValue(tempat + ', ' + date) };
    }

    function ttlLine(lines) {
        for (let i = 0; i < lines.length; i++) {
            if (STOP_RE.test(lines[i].replace(/\s+/g, '')) && !looksLikeTtlLabel(lines[i])) continue;
            const hit = ttlFromLine(lines[i]);
            if (hit && (looksLikeTtlLabel(lines[i]) || looksLikeTtlLabel(lines[i - 1] || ''))) return hit;
        }
        return null;
    }

    function parseIdentity(text) {
        const out = { nik: '', nama: '', ttl: '', tempat: '', tanggalLahir: '', alamat: '', rtRw: '', kelurahan: '', kecamatan: '', provinsi: '', pekerjaan: '', lines: [] };
        if (!text) return out;

        const normalized = text.replace(/\r/g, '\n').replace(/[ \t]+/g, ' ');
        const lines = normalized.split('\n').map(function (l) { return l.trim(); }).filter(function (l) { return l.length; });
        out.lines = lines;

        out.nik = findNik(normalized, lines);

        let nama = grabValue(lines, /^n\s*a\s*m\s*a\s*[:.]?\s*(.*)/i);
        if (!nama) {
            const im = normalized.match(/nama\s*[:.]?\s*([A-Z][A-Za-z .'-]{3,})/);
            if (im) nama = im[1];
        }
        if (nama) {
            nama = nama.replace(/\d/g, ' ').replace(/[^\w .'-]/g, ' ').replace(/\s+/g, ' ').trim();
            // Buang label yang menempel di ekor nama.
            nama = nama.split(/\s+(?:TEMPAT|TGL|AGAMA|ALAMAT|NIK|RT)\b/i)[0].trim();
            if (nama.length >= 3 && nama.length <= 80) out.nama = nama.toUpperCase();
        }

        let ttl = grabValue(lines, /^tempat\s*\/?\s*tgl\s*lahir\s*[:.]?\s*(.*)/i);
        if (ttl) {
            out.ttl = cleanValue(ttl);
            const d = normalizeDate(out.ttl);
            if (d) out.tanggalLahir = d;
            const comma = out.ttl.indexOf(',');
            if (comma > 1) out.tempat = out.ttl.slice(0, comma).trim();
        } else {
            const tempat = grabValue(lines, /^tempat\s*lahir\s*[:.]?\s*(.*)/i);
            const tgl = grabValue(lines, /^tgl\s*lahir\s*[:.]?\s*(.*)/i) || grabValue(lines, /^tanggal\s*lahir\s*[:.]?\s*(.*)/i);
            if (tempat || tgl) {
                const t = cleanValue([tempat, tgl].filter(Boolean).join(', '));
                out.ttl = t;
                const d = normalizeDate(t);
                if (d) {
                    out.tanggalLahir = d;
                    out.tempat = t.slice(0, t.length - d.length).replace(/[,\s]+$/, '');
                }
            }
        }

        // Label tidak terbaca sama sekali: cari baris yang memuat tanggal dan
        // nilai tempat, lalu pakai NIK sebagai sumber tanggal terakhir.
        if (!out.ttl) {
            const hit = ttlLine(lines);
            if (hit) {
                out.ttl = hit.ttl;
                out.tempat = hit.tempat;
                out.tanggalLahir = hit.tanggalLahir;
            }
        }
        if (!out.tanggalLahir && out.nik) out.tanggalLahir = nikBirthdate(out.nik);

        out.alamat = grabValue(lines, /^a\s*l\s*a\s*m\s*a\s*t\s*[:.]?\s*(.*)/i);
        out.rtRw = grabValue(lines, /^rt\s*[\/\\.]?\s*(?:rw)?\s*[:.]?\s*(.*)/i);
        out.kelurahan = grabValue(lines, /^kel\s*[.\/]\s*desa\s*[:.]?\s*(.*)/i);
        out.kecamatan = grabValue(lines, /^kecamatan\s*[:.]?\s*(.*)/i);
        out.provinsi = grabValue(lines, /^provinsi\s*[:.]?\s*(.*)/i);
        out.pekerjaan = grabValue(lines, /^pekerjaan\s*[:.]?\s*(.*)/i);

        // Kepala kartu mencetak "PROVINSI X    KABUPATEN Y" dalam satu baris.
        out.provinsi = (out.provinsi || '').split(/\b(?:KABUPATEN|KOTA|KECAMATAN)\b/i)[0].trim();
        out.rtRw = (out.rtRw || '').replace(/^.*?RT\s*[:.]?\s*/i, '').trim();

        // Garis alamat KTP bersambung sampai 3 baris; RT/RW, kelurahan,
        // kecamatan dan provinsi dicetak di kolom bawah. Disatukan supaya
        // kolom alamat benar-benar memakai isi kartu, bukan satu baris pertama.
        out.alamatLengkap = joinAlamat(out);

        return out;
    }

    function joinAlamat(f) {
        const parts = [f.alamat, f.rtRw, f.kelurahan, f.kecamatan, f.provinsi]
            .map(function (p) { return (p || '').trim(); })
            .filter(Boolean);
        const seen = [];
        for (const p of parts) {
            if (!seen.some(function (s) { return s.toLowerCase().indexOf(p.toLowerCase()) >= 0; })) seen.push(p);
        }
        return cleanValue(seen.join(', ')).slice(0, 250);
    }

    function nikScore(v) {
        if (!v || v.length !== 16) return -1;
        return 1 + validness(v);
    }

    function textScore(v) {
        const clean = (v || '').trim();
        if (!clean) return 0;
        let s = Math.min(3, clean.length / 6);
        if (/\d/.test(clean) && !/[A-Za-z]/.test(clean)) s -= 2;
        if (STOP_RE.test(clean.replace(/\s+/g, ''))) s -= 1;
        return s;
    }

    // Dua pass menghasilkan potongan yang saling melengkapi, tapi masing-masing
    // juga bisa merusak field yang sudah benar. Jadi dipilih per field, bukan
    // satu hasil utuh menggantikan yang lain.
    function mergeResults(results) {
        const keys = ['nama', 'ttl', 'alamat', 'rtRw', 'kelurahan', 'kecamatan', 'provinsi', 'pekerjaan'];
        const out = {};
        for (const k of keys) {
            let best = '';
            let bestScore = 0;
            for (const r of results) {
                const s = k === 'ttl'
                    ? (/\d{4}/.test(r[k] || '') ? 10 : 0) + textScore(r[k])
                    : textScore(r[k]);
                if (s > bestScore) { bestScore = s; best = r[k]; }
            }
            out[k] = best || '';
        }
        out.alamatLengkap = joinAlamat(out);
        out.lines = results[0].lines;

        let nik = '';
        let nikScoreBest = 0;
        for (const r of results) {
            const s = nikScore(r.nik);
            if (s > nikScoreBest) { nikScoreBest = s; nik = r.nik; }
        }
        out.nik = nik;
        out.tempat = out.ttl ? out.ttl.split(',')[0].trim() : '';
        out.tanggalLahir = normalizeDate(out.ttl || '');
        if (!out.tanggalLahir) {
            for (const r of results) {
                if (r.tanggalLahir) { out.tanggalLahir = r.tanggalLahir; break; }
            }
        }
        return out;
    }

    /* ---------- REKOGNISI ---------- */

    async function recognize(source, hooks) {
        const cb = hooks || {};
        if (workerBusy) throw new Error('OCR masih berjalan');
        workerBusy = true;

        try {
            cb.onStage && cb.onStage('Menyiapkan foto…');
            const prepared = prepare(source);
            if (!prepared) throw new Error('gambar tidak dapat dibaca');

            const w = await getWorker(cb.onProgress);

            // Citra kelabu mengalahkan hasil binarisasi pada semua uji kartu:
            // pola guilloche KTP ikut menebal saat di-threshold dan LSTM
            // membacanya sebagai huruf.
            cb.onStage && cb.onStage('Membaca teks…');
            await w.setParameters({
                tessedit_pageseg_mode: '6',
                preserve_interword_spaces: '1',
                user_defined_dpi: '300',
            });
            const first = await w.recognize(prepared.gray);
            const results = [parseIdentity(first.data.text || '')];
            let text = first.data.text || '';
            let confidence = first.data.confidence || 0;

            // Pass kedua hanya bila kartu utama belum terbaca: PSM 11 di atas
            // citra biner adalah pandangan berbeda yang menyelamatkan baris
            // yang tertutup foto atau cip.
            if (!results[0].nik || !results[0].nama) {
                cb.onStage && cb.onStage('Melengkapi bacaan…');
                await w.setParameters({ tessedit_pageseg_mode: '11' });
                const second = await w.recognize(prepared.binary);
                const extra = second.data.text || '';
                results.push(parseIdentity(extra));
                results.push(parseIdentity(text + '\n' + extra));
                if (second.data.confidence > confidence) confidence = second.data.confidence;
                text = text + '\n' + extra;
            }

            return {
                text: text,
                confidence: confidence,
                fields: mergeResults(results),
                passes: results.length > 1 ? 2 : 1,
                prepared: prepared,
            };
        } finally {
            workerBusy = false;
        }
    }

    function quality(f) {
        return (f.nik && f.nik.length === 16 ? 2 : 0)
            + (f.nama ? 1 : 0) + (f.ttl ? 1 : 0) + (f.alamat ? 1 : 0)
            + (f.kelurahan || f.kecamatan ? 1 : 0);
    }

    global.KtpOcr = {
        recognize: recognize,
        prepare: prepare,
        PREP_DEFAULT: PREP_DEFAULT,
        parseIdentity: parseIdentity,
        mergeResults: mergeResults,
        quality: quality,
        normalizeDate: normalizeDate,
        version: 2,
    };
})(window);
