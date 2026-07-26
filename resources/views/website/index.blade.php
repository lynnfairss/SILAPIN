<!DOCTYPE html>
<html>

<head>

    <title>SILAPIN</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-5">

    <h2 class="text-center">

        SILAPIN

    </h2>

    <p class="text-center">

        Sistem Informasi Peminjaman Inventaris

    </p>

    <hr>

    <div class="row">

        @foreach($inventaris as $item)

        <div class="col-md-4 mb-4">

            <div class="card h-100">

                @if($item->foto)

                <img src="{{ asset('storage/'.$item->foto) }}"
                class="card-img-top img-fluid"
                style="height:300px;width:100%;object-fit:cover;">

                @endif

                <div class="card-body">

                    <h5>

                        {{ $item->nama_barang }}

                    </h5>

                    <p>

                        Kategori :

                        {{ $item->kategori->nama_kategori }}

                    </p>

                    <p>

                        Stok :

                        {{ $item->stok }}

                    </p>

                    <a href="#"
                       class="btn btn-primary w-100">

                        Ajukan Peminjaman

                    </a>

                </div>

            </div>

        </div>

        @endforeach

    </div>

</div>

</body>

</html>