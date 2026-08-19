<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\Instansi;
use App\Models\Inventaris;
use App\Models\Kategori;
use App\Models\DetailPermohonan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PermohonanController extends Controller
{
    public function form()
    {
        $instansi = Instansi::all();
        $kategori = Kategori::all();
        $inventaris = Inventaris::with('kategori', 'fotos')->where('stok', '>', 0)->get();

        $instansiTipe = $instansi->mapWithKeys(fn($item) => [$item->id => $item->effective_tipe_identitas]);

        $daftarKota = [
            'Aceh' => ['Banda Aceh', 'Lhokseumawe', 'Langsa', 'Sabang', 'Subulussalam', 'Bireuen', 'Jantho', 'Takengon', 'Kutacane', 'Singkil', 'Sigli', 'Calang', 'Tapaktuan', 'Blangkejeren', 'Meulaboh', 'Sinabang'],
            'Sumatera Utara' => ['Medan', 'Binjai', 'Tebing Tinggi', 'Pematangsiantar', 'Sibolga', 'Padang Sidempuan', 'Gunungsitoli', 'Lubuk Pakam', 'Tanjung Balai', 'Kabanjahe', 'Kisaran'],
            'Sumatera Barat' => ['Padang', 'Bukittinggi', 'Padang Panjang', 'Payakumbuh', 'Solok', 'Sawahlunto', 'Pariaman'],
            'Riau' => ['Pekanbaru', 'Dumai', 'Bengkalis', 'Selat Panjang', 'Rengat', 'Bangkinang', 'Siak Sri Indrapura'],
            'Kepulauan Riau' => ['Batam', 'Tanjung Pinang', 'Tanjung Balai Karimun', 'Bintan', 'Natuna'],
            'Jambi' => ['Jambi', 'Sungai Penuh', 'Muara Bungo', 'Sarolangun', 'Muara Tebo', 'Sengeti', 'Bangko'],
            'Sumatera Selatan' => ['Palembang', 'Lubuklinggau', 'Pagar Alam', 'Prabumulih', 'Muara Enim', 'Baturaja', 'Kayuagung'],
            'Bangka Belitung' => ['Pangkal Pinang', 'Sungailiat', 'Tanjung Pandan', 'Muntok', 'Pangkalan Balai', 'Koba', 'Manggar'],
            'Lampung' => ['Bandar Lampung', 'Metro', 'Kota Bumi', 'Pringsewu', 'Kalianda', 'Gunung Sugih', 'Liwa'],
            'DKI Jakarta' => ['Jakarta', 'Jakarta Pusat', 'Jakarta Utara', 'Jakarta Barat', 'Jakarta Selatan', 'Jakarta Timur'],
            'Banten' => ['Serang', 'Cilegon', 'Tangerang', 'Tangerang Selatan', 'Pandeglang', 'Lebak'],
            'Jawa Barat' => ['Bandung', 'Cimahi', 'Bogor', 'Depok', 'Bekasi', 'Sukabumi', 'Cianjur', 'Garut', 'Tasikmalaya', 'Banjar', 'Ciamis', 'Kuningan', 'Majalengka', 'Sumedang', 'Indramayu', 'Cirebon', 'Subang', 'Purwakarta', 'Karawang', 'Pangandaran'],
            'Jawa Tengah' => ['Semarang', 'Salatiga', 'Magelang', 'Temanggung', 'Wonosobo', 'Purworejo', 'Kebumen', 'Cilacap', 'Banjarnegara', 'Purbalingga', 'Banyumas', 'Tegal', 'Pekalongan', 'Pemalang', 'Kendal', 'Demak', 'Grobogan', 'Blora', 'Rembang', 'Pati', 'Kudus', 'Jepara', 'Batang', 'Boyolali', 'Klaten', 'Sukoharjo', 'Karanganyar', 'Wonogiri', 'Sragen', 'Surakarta'],
            'DI Yogyakarta' => ['Yogyakarta', 'Sleman', 'Bantul', 'Kulon Progo', 'Gunungkidul'],
            'Jawa Timur' => ['Surabaya', 'Malang', 'Batu', 'Pasuruan', 'Probolinggo', 'Situbondo', 'Banyuwangi', 'Bondowoso', 'Jember', 'Lumajang', 'Pamekasan', 'Sumenep', 'Sampang', 'Bangkalan', 'Gresik', 'Lamongan', 'Tuban', 'Bojonegoro', 'Ngawi', 'Magetan', 'Madiun', 'Ponorogo', 'Pacitan', 'Blitar', 'Kediri', 'Tulungagung', 'Trenggalek', 'Nganjuk', 'Mojokerto', 'Jombang', 'Sidoarjo'],
            'Bali' => ['Denpasar', 'Badung', 'Gianyar', 'Klungkung', 'Bangli', 'Karangasem', 'Buleleng', 'Jembrana', 'Tabanan'],
            'Nusa Tenggara Barat' => ['Mataram', 'Bima', 'Sumbawa Besar', 'Dompu', 'Praya', 'Selong', 'Taliwang'],
            'Nusa Tenggara Timur' => ['Kupang', 'Ende', 'Maumere', 'Ruteng', 'Labuan Bajo', 'Atambua', 'Soe', 'Kefamenanu', 'Waingapu', 'Waikabubak', 'Bajawa', 'Kalabahi', 'Lewoleba', 'Tambolaka'],
            'Kalimantan Barat' => ['Pontianak', 'Singkawang', 'Sintang', 'Ketapang', 'Sambas', 'Ngabang', 'Mempawah', 'Putussibau', 'Sanggau', 'Sekadau', 'Nanga Pinoh'],
            'Kalimantan Tengah' => ['Palangka Raya', 'Sampit', 'Pangkalan Bun', 'Kuala Kapuas', 'Muara Teweh', 'Buntok', 'Kasongan', 'Puruk Cahu', 'Tamiang Layang', 'Nanga Bulik'],
            'Kalimantan Selatan' => ['Banjarmasin', 'Banjarbaru', 'Martapura', 'Kandangan', 'Barabai', 'Amuntai', 'Kotabaru', 'Tanjung', 'Batulicin', 'Marabahan', 'Paringin', 'Rantau'],
            'Kalimantan Timur' => ['Samarinda', 'Balikpapan', 'Bontang', 'Tenggarong', 'Sangatta', 'Tanah Grogot', 'Sendawar', 'Penajam'],
            'Kalimantan Utara' => ['Tanjung Selor', 'Tarakan', 'Nunukan', 'Malinau', 'Tana Tidung'],
            'Sulawesi Utara' => ['Manado', 'Bitung', 'Tomohon', 'Kotamobagu', 'Tondano', 'Airmadidi', 'Amurang', 'Tahuna'],
            'Gorontalo' => ['Gorontalo', 'Limboto', 'Suwawa', 'Tilamuta', 'Kwandang', 'Marisa'],
            'Sulawesi Tengah' => ['Palu', 'Donggala', 'Toli-Toli', 'Buol', 'Luwuk', 'Poso', 'Tentena', 'Ampana', 'Parigi', 'Bungku', 'Kolonodale', 'Salakan'],
            'Sulawesi Selatan' => ['Makassar', 'Parepare', 'Palopo', 'Watampone', 'Watansoppeng', 'Sengkang', 'Pangkajene', 'Maros', 'Sungguminasa', 'Takalar', 'Jeneponto', 'Bantaeng', 'Bulukumba', 'Sinjai', 'Benteng', 'Makale', 'Rantepao', 'Masamba', 'Pinrang', 'Barru', 'Enrekang', 'Sidrap'],
            'Sulawesi Tenggara' => ['Kendari', 'Baubau', 'Unaaha', 'Andoolo', 'Raha', 'Pasarwajo', 'Wangi-Wangi', 'Kolaka', 'Lasalimu', 'Kulisusu', 'Bombana', 'Rumbia'],
            'Maluku' => ['Ambon', 'Tual', 'Saumlaki', 'Masohi', 'Namlea', 'Namrole'],
            'Maluku Utara' => ['Sofifi', 'Ternate', 'Tidore Kepulauan', 'Jailolo', 'Tobelo', 'Maba', 'Labuha', 'Sanana'],
            'Papua Barat' => ['Manokwari', 'Sorong', 'Fakfak', 'Kaimana', 'Waisai', 'Bintuni', 'Teminabuan', 'Ayamaru'],
            'Papua' => ['Jayapura', 'Wamena', 'Timika', 'Nabire', 'Biak', 'Serui', 'Merauke', 'Sarmi', 'Oksibil'],
        ];

        return view('peminjam.index', compact('instansi', 'kategori', 'inventaris', 'instansiTipe', 'daftarKota'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_peminjam'    => 'required|string|max:150',
            'nik'              => 'required|string|max:30',
            'jabatan'          => 'required|string|max:100',
            'telepon'          => 'required|string|max:15|regex:/^[0-9]+$/',
            'tempat_lahir'     => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z][A-Za-z .\x27-]{0,99}$/'],
            'tanggal_lahir'    => ['nullable', 'regex:/^\d{2}-\d{2}-\d{4}$/', function ($attribute, $value, $fail) {
                $parts = explode('-', $value);
                if (count($parts) !== 3 || !checkdate((int) $parts[1], (int) $parts[0], (int) $parts[2])) {
                    $fail('Tanggal lahir tidak valid.');
                    return;
                }
                $d = \DateTime::createFromFormat('!d-m-Y', $value);
                if ($d && $d > new \DateTime('today')) {
                    $fail('Tanggal lahir tidak boleh di masa depan.');
                }
            }],
            'instansi_id'      => 'required',
            'nama_instansi_lain' => 'nullable|required_if:instansi_id,lainnya|string|max:100',
            'tanggal_pinjam'   => 'required|date',
            'tanggal_kembali'  => 'required|date|after_or_equal:tanggal_pinjam',
            'keperluan'        => 'required|string',
            'foto_ktp'         => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'surat_tugas'      => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'inventaris'       => 'required|array|min:1',
            'inventaris.*'     => 'exists:inventaris,id',
            'jumlah'           => 'required|array',
            'jumlah.*'         => 'integer|min:1',
        ], [
            'jabatan.required'         => 'Jabatan wajib diisi.',
            'instansi_id.required'     => 'Instansi wajib dipilih.',
            'nama_instansi_lain.required_if' => 'Nama Instansi wajib diisi.',
            'foto_ktp.required'        => 'Foto KTP wajib diunggah.',
            'tempat_lahir.regex'  => 'Tempat lahir hanya boleh berisi huruf, spasi, titik, atau tanda hubung.',
            'tanggal_lahir.regex' => 'Format tanggal lahir harus DD-MM-YYYY (contoh: 15-08-1990).',
        ]);

        $fotoKtp = null;
        if ($request->hasFile('foto_ktp')) {
            $fotoKtp = $request->file('foto_ktp')->store('foto-ktp', 'public');
        }

        $suratTugas = null;
        if ($request->hasFile('surat_tugas')) {
            $suratTugas = $request->file('surat_tugas')->store('surat-tugas', 'public');
        }

        $tanggalLahir = null;
        if ($request->filled('tanggal_lahir')) {
            $tgl = \DateTime::createFromFormat('d-m-Y', $request->tanggal_lahir);
            if ($tgl) {
                $tanggalLahir = $tgl->format('Y-m-d');
            }
        }

        $nomor = 'SP-' . strtoupper(date('dmy')) . '-' . strtoupper(substr(uniqid(), -6));

        DB::transaction(function () use ($request, $nomor, $fotoKtp, $suratTugas, $tanggalLahir) {
            $permohonan = Permohonan::create([
                'nomor_permohonan'   => $nomor,
                'instansi_id'        => is_numeric($request->instansi_id) ? $request->instansi_id : null,
                'nama_instansi_lain' => $request->instansi_id === 'lainnya' ? $request->nama_instansi_lain : null,
                'nama_peminjam'      => $request->nama_peminjam,
                'nik'                => $request->nik,
                'jabatan'            => $request->jabatan,
                'telepon'            => $request->telepon,
                'tempat_lahir'       => $request->tempat_lahir,
                'tanggal_lahir'      => $tanggalLahir,
                'tanggal_pinjam'     => $request->tanggal_pinjam,
                'tanggal_kembali'    => $request->tanggal_kembali,
                'keperluan'          => $request->keperluan,
                'status'             => 'Menunggu',
                'foto_ktp'           => $fotoKtp,
                'surat_tugas'        => $suratTugas,
            ]);

            foreach ($request->inventaris as $key => $inventarisId) {
                $jml = $request->jumlah[$inventarisId] ?? 1;
                DetailPermohonan::create([
                    'permohonan_id'  => $permohonan->id,
                    'inventaris_id'  => $inventarisId,
                    'jumlah'         => $jml,
                ]);
            }

            \App\Models\PermohonanStatusLog::create([
                'permohonan_id' => $permohonan->id,
                'status_lama'   => null,
                'status_baru'   => 'Menunggu',
                'catatan'       => 'Permohonan diajukan oleh peminjam.',
                'user_id'       => null,
            ]);
        });

        $permohonan = Permohonan::with('detailPermohonan.inventaris', 'instansi')
            ->where('nomor_permohonan', $nomor)
            ->first();

        return redirect()->route('peminjam.cek-status', ['nomor' => $nomor])
            ->with('success', 'Permohonan berhasil dikirim!');
    }

    public function cekStatus(Request $request)
    {
        if ($request->expectsJson()) {
            $permohonan = Permohonan::with('detailPermohonan.inventaris', 'instansi')
                ->where('nomor_permohonan', $request->nomor)
                ->first();

            if (!$permohonan) {
                return response()->json([
                    'error' => 'Permohonan dengan nomor tersebut tidak ditemukan.',
                ], 404);
            }

            return response()->json([
                'nomor'           => $permohonan->nomor_permohonan,
                'nama'            => $permohonan->nama_peminjam,
                'instansi'        => $permohonan->instansi?->nama_instansi ?? $permohonan->nama_instansi_lain ?? '-',
                'status'          => $permohonan->status,
                'tanggal_pinjam'  => $permohonan->tanggal_pinjam,
                'tanggal_kembali' => $permohonan->tanggal_kembali,
                'catatan'         => $permohonan->catatan_admin,
                'created_at'      => $permohonan->created_at?->format('d M Y H:i'),
                'barang'          => $permohonan->detailPermohonan->map(fn ($d) => [
                    'nama'   => $d->inventaris?->nama_barang ?? 'Barang #'.$d->inventaris_id,
                    'kode'   => $d->inventaris?->kode_barang ?? '',
                    'jumlah' => $d->jumlah,
                ]),
            ]);
        }

        $permohonan = null;

        if ($request->filled('nomor')) {
            $permohonan = Permohonan::with('detailPermohonan.inventaris', 'instansi')
                ->where('nomor_permohonan', $request->nomor)
                ->first();

            if (!$permohonan) {
                return redirect()->route('peminjam.cek-status')
                    ->with('error', 'Permohonan dengan nomor "' . $request->nomor . '" tidak ditemukan.');
            }
        }

        return view('peminjam.cek-status', compact('permohonan'));
    }

    public function downloadSurat(Permohonan $permohonan)
    {
        $permohonan->load('detailPermohonan.inventaris', 'instansi');

        return view('peminjam.surat', compact('permohonan'));
    }
}
