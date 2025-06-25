<table class="table table-bordered table-hover table-sm" id="depositoTable">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Nasabah</th>
            <th>Nomor Rekening</th>
            <th>Jumlah Deposito</th>
            <th>Jatuh Tempo</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($depositos as $key => $deposito)
        <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ $deposito->nasabah->nama }}</td>
            <td>{{ $deposito->rekening }}</td>
            <td>{{ number_format($deposito->jumlah, 0, ',', '.') }}</td>
            <td>{{ \Carbon\Carbon::parse($deposito->jatuh_tempo)->format('d-m-Y') }}</td>
            <td>
                <a href="{{ route('deposito.show', $deposito->id) }}" class="btn btn-sm btn-info">Detail</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
