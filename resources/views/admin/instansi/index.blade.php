@extends('adminlte::page')

@section('title', 'Data Instansi')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Data Instansi</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalTambah">
            <i class="fas fa-plus mr-1"></i> Tambah Instansi
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
            <h3 class="card-title">Daftar Instansi</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead>
                    <tr class="text-center">
                        <th style="width: 60px;">No</th>
                        <th>Nama Instansi</th>
                        <th>Alamat</th>
                        <th>Telepon</th>
                        <th style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($instansi as $item)
                        <tr>
                            <td class="text-center">{{ $instansi->firstItem() + $loop->index }}</td>
                            <td>{{ $item->nama_instansi }}</td>
                            <td>{{ $item->alamat ?? '-' }}</td>
                            <td>{{ $item->telepon ?? '-' }}</td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm btn-edit"
                                        data-id="{{ $item->id }}"
                                        data-nama="{{ $item->nama_instansi }}"
                                        data-alamat="{{ $item->alamat }}"
                                        data-telepon="{{ $item->telepon }}"
                                        data-toggle="modal"
                                        data-target="#modalEdit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('instansi.destroy', $item->id) }}" 
                                      method="POST" 
                                      class="d-inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Yakin ingin menghapus data ini?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-3 text-muted">
                                Belum ada data instansi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($instansi->hasPages())
            <div class="card-footer clearfix">
                {{ $instansi->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL TAMBAH INSTANSI -->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('instansi.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title">Tambah Instansi</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nama_instansi">Nama Instansi</label>
                            <input type="text" 
                                   name="nama_instansi" 
                                   id="nama_instansi" 
                                   class="form-control" 
                                   required>
                        </div>
                        <div class="form-group">
                            <label for="alamat">Alamat</label>
                            <textarea name="alamat" 
                                      id="alamat" 
                                      class="form-control" 
                                      rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="telepon">Telepon</label>
                            <input type="text" 
                                   name="telepon" 
                                   id="telepon" 
                                   class="form-control">
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

    <!-- MODAL EDIT INSTANSI -->
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEdit" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Edit Instansi</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_nama_instansi">Nama Instansi</label>
                            <input type="text" 
                                   name="nama_instansi" 
                                   id="edit_nama_instansi" 
                                   class="form-control" 
                                   required>
                        </div>
                        <div class="form-group">
                            <label for="edit_alamat">Alamat</label>
                            <textarea name="alamat" 
                                      id="edit_alamat" 
                                      class="form-control" 
                                      rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="edit_telepon">Telepon</label>
                            <input type="text" 
                                   name="telepon" 
                                   id="edit_telepon" 
                                   class="form-control">
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
        $(function() {
            $('.btn-edit').click(function() {
                let id = $(this).data('id');
                let nama = $(this).data('nama');
                let alamat = $(this).data('alamat');
                let telepon = $(this).data('telepon');

                $('#edit_nama_instansi').val(nama);
                $('#edit_alamat').val(alamat);
                $('#edit_telepon').val(telepon);

                // Dinamis berdasarkan URL dasar Laravel
                $('#formEdit').attr('action', '{{ url("instansi") }}/' + id);
            });
        });
    </script>
@stop