/**
 * Konfigurasi Tailwind khusus halaman form peminjaman.
 *
 * Halaman ini memakai Bootstrap 5, jadi preflight Tailwind dimatikan agar
 * tidak menimpa style Bootstrap. Hasil build disimpan sebagai CSS statis di
 * public/css/peminjam-tailwind.css sehingga browser tidak perlu menjalankan
 * compiler Tailwind (cdn.tailwindcss.com).
 */
module.exports = {
    content: [
        './resources/views/peminjam/index.blade.php',
    ],
    corePlugins: {
        preflight: false,
    },
    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', 'sans-serif'],
            },
            colors: {
                brand: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                    950: '#0f172a',
                },
            },
        },
    },
};
