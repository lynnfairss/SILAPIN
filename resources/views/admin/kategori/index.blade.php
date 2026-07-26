@extends('adminlte::page')

@section('title', 'Data Kategori')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Data Kategori</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalTambah">
            <i class="fas fa-plus mr-1"></i> Tambah Kategori
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
            <h3 class="card-title">Daftar Kategori</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0">
                <thead>
                    <tr class="text-center">
                        <th style="width: 60px;">No</th>
                        <th>Nama Kategori</th>
                        <th>Keterangan</th>
                        <th style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kategori as $item)
                        <tr>
                            <td class="text-center">{{ $kategori->firstItem() + $loop->index }}</td>
                            <td>{{ $item->nama_kategori }}</td>
                            <td>{{ $item->keterangan ?? '-' }}</td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm btn-edit"
                                        data-id="{{ $item->id }}"
                                        data-nama="{{ $item->nama_kategori }}"
                                        data-keterangan="{{ $item->keterangan }}"
                                        data-toggle="modal"
                                        data-target="#modalEdit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('kategori.destroy', $item->id) }}" 
                                      method="POST" 
                                      class="d-inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Yakin ingin menghapus data?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-3 text-muted">
                                Belum ada data kategori.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($kategori->hasPages())
            <div class="card-footer clearfix">
                {{ $kategori->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL TAMBAH KATEGORI -->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('kategori.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title">Tambah Kategori</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nama_kategori">Nama Kategori</label>
                            <input type="text" 
                                   name="nama_kategori" 
                                   id="nama_kategori" 
                                   class="form-control" 
                                   required>
                        </div>
                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <textarea name="keterangan" 
                                      id="keterangan" 
                                      class="form-control" 
                                      rows="3"></textarea>
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

    <!-- MODAL EDIT KATEGORI -->
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEdit" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Edit Kategori</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_nama_kategori">Nama Kategori</label>
                            <input type="text" 
                                   name="nama_kategori" 
                                   id="edit_nama_kategori" 
                                   class="form-control" 
                                   required>
                        </div>
                        <div class="form-group">
                            <label for="edit_keterangan">Keterangan</label>
                            <textarea name="keterangan" 
                                      id="edit_keterangan" 
                                      class="form-control" 
                                      rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save mr-1"></i> Update
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
            $('.btn-edit').click(function() {
                let id = $(this).data('id');
                let nama = $(this).data('nama');
                let keterangan = $(this).data('keterangan');

                $('#edit_nama_kategori').val(nama);
                $('#edit_keterangan').val(keterangan);

                // Menggunakan route helper dasar Laravel di JS
                $('#formEdit').attr('action', '{{ url("kategori") }}/' + id);
            });
        });
    </script>
@stop