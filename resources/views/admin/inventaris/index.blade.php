@extends('adminlte::page')

@section('title', 'Data Inventaris')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Data Inventaris</h1>
    <button class="btn btn-primary" data-toggle="modal" data-target="#modalTambah">
        <i class="fas fa-plus mr-1"></i> Tambah Inventaris
    </button>
</div>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Inventaris</h3>
    </div>

    <div class="card-body p-0 table-responsive">
        <table class="table table-bordered table-hover table-striped m-0">
            <thead class="text-center">
                <tr>
                    <th width="60">No</th>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th>Kondisi</th>
                    <th>Foto</th>
                    <th width="160">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($inventaris as $item)
                <tr>
                    <td class="text-center">{{ $inventaris->firstItem() + $loop->index }}</td>
                    <td>{{ $item->kode_barang }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->kategori->nama_kategori ?? '-' }}</td>
                    <td class="text-center">{{ $item->stok }}</td>
                    <td class="text-center">
                        @if($item->kondisi == 'Baik')
                            <span class="badge badge-success">Baik</span>

                        @elseif($item->kondisi == 'Rusak Ringan')
                            <span class="badge badge-warning">Rusak Ringan</span>

                        @elseif($item->kondisi == 'Rusak Berat')
                            <span class="badge badge-danger">Rusak Berat</span>

                        @else
                            <span class="badge badge-secondary">
                                {{ $item->kondisi }}
                            </span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($item->foto)
                            <img src="{{ asset('storage/' . $item->foto) }}" width="60" class="img-thumbnail">
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <button 
                            class="btn btn-warning btn-sm btn-edit"
                            data-id="{{ $item->id }}"
                            data-kategori="{{ $item->kategori_id }}"
                            data-kode="{{ $item->kode_barang }}"
                            data-nama="{{ $item->nama_barang }}"
                            data-stok="{{ $item->stok }}"
                            data-kondisi="{{ $item->kondisi }}"
                            data-deskripsi="{{ $item->deskripsi }}"
                            data-foto="{{ $item->foto ? asset('storage/' . $item->foto) : '' }}"
                            data-toggle="modal" 
                            data-target="#modalEdit"
                            title="Edit Data">
                            <i class="fas fa-edit"></i>
                        </button>

                        <form action="{{ route('inventaris.destroy', $item->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')" title="Hapus Data">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">Belum ada data inventaris.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($inventaris->hasPages())
    <div class="card-footer clearfix">
        {{ $inventaris->links() }}
    </div>
    @endif
</div>

<!-- =====================================
     MODAL TAMBAH INVENTARIS
====================================== -->
<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('inventaris.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Tambah Inventaris</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kategori</label>
                                <select name="kategori_id" class="form-control" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($kategori as $k)
                                        <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kode Barang</label>
                                <input type="text" name="kode_barang" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Nama Barang</label>
                        <input type="text" name="nama_barang" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Stok</label>
                                <input type="number" name="stok" class="form-control" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kondisi</label>
                                <select name="kondisi" class="form-control" required>
                                    <option value="Baik">Baik</option>
                                    <option value="Rusak Ringan">Rusak Ringan</option>
                                    <option value="Rusak Berat">Rusak Berat</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" rows="3" class="form-control"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Foto Barang</label>
                        <input type="file" name="foto" class="form-control-file">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =====================================
     MODAL EDIT INVENTARIS
====================================== -->
 <div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formEdit" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white">Edit Inventaris</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kategori</label>
                                <select name="kategori_id" id="edit_kategori_id" class="form-control" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($kategori as $k)
                                        <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kode Barang</label>
                                <input type="text" name="kode_barang" id="edit_kode_barang" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Nama Barang</label>
                        <input type="text" name="nama_barang" id="edit_nama_barang" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Stok</label>
                                <input type="number" name="stok" id="edit_stok" class="form-control" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kondisi</label>
                                <select name="kondisi" id="edit_kondisi" class="form-control" required>
                                    <option value="baik">Baik</option>
                                    <option value="rusak ringan">Rusak Ringan</option>
                                    <option value="rusak berat">Rusak Berat</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" id="edit_deskripsi" rows="3" class="form-control"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Foto Barang (Opsional)</label>
                        <input type="file" name="foto" class="form-control-file">
                        <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah foto.</small>
                        <div id="preview_foto_wrapper" class="mt-2 d-none">
                            <p class="mb-1 text-sm font-weight-bold">Foto saat ini:</p>
                            <img id="edit_preview_foto" src="" width="100" class="img-thumbnail">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-white">
                        <i class="fas fa-save mr-1"></i> Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.btn-edit').on('click', function() {
            let id = $(this).data('id');
            let kategori = $(this).data('kategori');
            let kode = $(this).data('kode');
            let nama = $(this).data('nama');
            let stok = $(this).data('stok');
            let kondisi = $(this).data('kondisi');
            let deskripsi = $(this).data('deskripsi');
            let foto = $(this).data('foto');

            // Set action URL pada form modal edit
            let url = "{{ route('inventaris.update', ':id') }}";
            url = url.replace(':id', id);
            $('#formEdit').attr('action', url);

            // Isikan nilai ke form input
            $('#edit_kategori_id').val(kategori);
            $('#edit_kode_barang').val(kode);
            $('#edit_nama_barang').val(nama);
            $('#edit_stok').val(stok);
            $('#edit_kondisi').val(kondisi);
            $('#edit_deskripsi').val(deskripsi);

            // Tampilkan foto pratinjau jika ada
            if (foto) {
                $('#edit_preview_foto').attr('src', foto);
                $('#preview_foto_wrapper').removeClass('d-none');
            } else {
                $('#preview_foto_wrapper').addClass('d-none');
            }
        });
    });
</script>
@stop