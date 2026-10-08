/**
 * HTTPS dev server — diperlukan untuk kamera (getUserMedia) via HP.
 * Kamera hanya jalan di konteks aman (https://), bukan http://<ip>:8000.
 *
 * Cara pakai:
 *   1. php artisan serve            (backend seperti biasa, port 8000)
 *   2. node dev-https.cjs           (proxy HTTPS, port 8443)
 *   3. Buka dari HP: https://192-168-1-33.nip.io:8443
 *      (nip.io resolve ke IP LAN via DNS publik; passkey WebAuthn DILARANG
 *       memakai IP langsung — Chrome menolak "invalid domain")
 *      Abaikan peringatan sertifikat: Lanjutkan / Advanced -> Proceed.
 *
 * Sertifikat self-signed: .ssl/cert.pem + .ssl/key.pem
 * (SAN: IP 192.168.1.33, 192.168.18.214, 127.0.0.1 + DNS localhost,
 *  silapin.local, 192-168-1-33.nip.io, 192.168.1.33.nip.io).
 */
const https = require('https');
const http = require('http');
const fs = require('fs');
const path = require('path');

const PORT = Number(process.env.HTTPS_PORT || 8443);
const TARGET = { host: '127.0.0.1', port: Number(process.env.BACKEND_PORT || 8000) };

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
    console.log(`HTTPS dev aktif : https://192-168-1-33.nip.io:${PORT}  (dan https://127.0.0.1:${PORT})`);
    console.log(`Proxy ke        : http://${TARGET.host}:${TARGET.port}`);
    console.log('Buka dari HP lewat URL https di atas; abaikan peringatan sertifikat.');
});
