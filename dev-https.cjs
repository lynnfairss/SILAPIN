/**
 * HTTPS dev server — diperlukan untuk kamera (getUserMedia) via HP.
 * Kamera hanya jalan di konteks aman (https://), bukan http://<ip>:8000.
 *
 * Cara pakai:
 *   1. php artisan serve            (backend seperti biasa, port 8000)
 *   2. node dev-https.cjs           (proxy HTTPS, port 8443)
 *   3. Buka dari HP lewat URL https yang dicetak saat server nyala,
 *      contoh https://192-168-1-16.nip.io:8443
 *      (nip.io resolve ke IP LAN via DNS publik; passkey WebAuthn DILARANG
 *       memakai IP langsung — Chrome menolak "invalid domain")
 *      Abaikan peringatan sertifikat, atau pasang .ssl/ca.pem di HP supaya
 *      gemboknya hijau (diperlukan kalau browser menolak WebAuthn di halaman
 *      bersertifikat error).
 *
 * Sertifikat self-signed: .ssl/cert.pem + .ssl/key.pem, ditandatangani
 * .ssl/ca.pem (SILAPIN Local Dev CA). Daftar SAN ada di .ssl/openssl.cnf;
 * tambah IP/hostname baru di sana lalu terbitkan ulang:
 *   openssl req -new -newkey rsa:2048 -nodes -keyout .ssl/key.pem \
 *     -out .ssl/leaf.csr -config .ssl/openssl.cnf
 *   openssl x509 -req -in .ssl/leaf.csr -CA .ssl/ca.pem -CAkey .ssl/ca.key \
 *     -CAcreateserial -out .ssl/cert.pem -days 397 -sha256 \
 *     -extfile .ssl/openssl.cnf -extensions v3_req
 */
const https = require('https');
const http = require('http');
const fs = require('fs');
const os = require('os');
const path = require('path');

const PORT = Number(process.env.HTTPS_PORT || 8443);
const TARGET = { host: '127.0.0.1', port: Number(process.env.BACKEND_PORT || 8000) };

/*
 * IP LAN dipakai untuk membentuk nama nip.io. Diurutkan supaya adapter
 * virtual (192.168.56.x, Docker, dll) tidak menang atas Wi-Fi sungguhan.
 */
function ipLan() {
    const kandidat = [];
    for (const daftar of Object.values(os.networkInterfaces())) {
        for (const antarmuka of daftar || []) {
            if (antarmuka.family !== 'IPv4' && antarmuka.addressType !== 'IPv4') continue;
            if (antarmuka.internal) continue;
            kandidat.push(antarmuka.address);
        }
    }
    const utama = (awalan) => kandidat.find((ip) => ip.startsWith(awalan));
    return utama('192.168.1.') || utama('192.168.') || utama('10.') || utama('172.')
        || kandidat[0] || '127.0.0.1';
}

const server = https.createServer({
    key: fs.readFileSync(path.join(__dirname, '.ssl', 'key.pem')),
    cert: fs.readFileSync(path.join(__dirname, '.ssl', 'cert.pem')),
}, (req, res) => {
    const headers = Object.assign({}, req.headers, {
        'X-Forwarded-Proto': 'https',
        'X-Forwarded-Host': req.headers.host || '',
    });
    const backend = http.request({
        host: TARGET.host,
        port: TARGET.port,
        method: req.method,
        path: req.url,
        headers,
    }, (bres) => {
        res.writeHead(bres.statusCode || 502, bres.headers);
        bres.pipe(res);
    });
    backend.on('error', () => {
        if (!res.headersSent) res.writeHead(502, { 'Content-Type': 'text/plain; charset=utf-8' });
        res.end('Backend mati. Jalankan dulu: php artisan serve');
    });
    req.pipe(backend);
});

server.on('upgrade', (req, socket, head) => {
    const backend = http.request({
        host: TARGET.host,
        port: TARGET.port,
        path: req.url,
        headers: Object.assign({}, req.headers, { 'X-Forwarded-Proto': 'https' }),
    });
    backend.on('upgrade', (bres, bsocket, bhead) => {
        socket.write('HTTP/1.1 101 Switching Protocols\r\n' +
            Object.entries(bres.headers).map(([k, v]) => `${k}: ${Array.isArray(v) ? v.join(', ') : v}`).join('\r\n') +
            '\r\n\r\n');
        if (bhead && bhead.length) socket.write(bhead);
        bsocket.pipe(socket).pipe(bsocket);
    });
    backend.on('error', () => socket.destroy());
    backend.end();
});

server.listen(PORT, '0.0.0.0', () => {
    const ip = ipLan();
    const hostNip = ip.split('.').join('-') + '.nip.io';
    console.log(`HTTPS dev aktif : https://${hostNip}:${PORT}  (juga https://${ip}:${PORT} dan https://127.0.0.1:${PORT})`);
    console.log(`Proxy ke        : http://${TARGET.host}:${TARGET.port}`);
    console.log('Buka dari HP lewat nama domain di atas (passkey menolak alamat IP).');
});
