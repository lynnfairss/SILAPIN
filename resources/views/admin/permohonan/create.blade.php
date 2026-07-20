<hr>

<h4>Pilih Inventaris</h4>

<table class="table table-bordered">

<thead>

<tr>

<th>Pilih</th>

<th>Barang</th>

<th>Kategori</th>

<th>Stok</th>

<th width="150">Jumlah</th>

</tr>

</thead>

<tbody>

@foreach($inventaris as $item)

<tr>

<td>

<input
type="checkbox"
name="inventaris[]"
value="{{ $item->id }}">

</td>

<td>

{{ $item->nama_barang }}

</td>

<td>

{{ $item->kategori->nama_kategori }}

</td>

<td>

{{ $item->stok }}

</td>

<td>

<input
type="number"
name="jumlah[{{ $item->id }}]"
class="form-control"
min="1"
value="1">

</td>

</tr>

@endforeach

</tbody>

</table>

<button class="btn btn-primary">

Simpan

</button>

</form>

</div>

</div>

@stop