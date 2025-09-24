<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
            font-size: 13px;
        }
        th, td {
            border: 1px solid #555;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background: #f0f0f0;
        }
    </style>
</head>
<body>
    <p>Selamat pagi, teman-teman CS,</p>

    <p>
        Berikut saya lampirkan data deposito yang akan jatuh tempo dalam
        <b>{{ $reminder->hari_sebelum_jt }} Hari</b> ke depan:
    </p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Nasabah</th>
                <th>Rekening</th>
                <th>Nominal</th>
                <th>Jatuh Tempo</th>
                <th>Jenis</th>
                <th>Kantor</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($depositos as $i => $d)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $d->nama_nasabah }}</td>
                <td>{{ $d->no_rekening }}</td>
                <td>Rp {{ number_format($d->nominal, 0, ',', '.') }}</td>
                <td>{{ \Carbon\Carbon::parse($d->tanggal_jatuh_tempo)->format('d M y') }}</td>
                <td>{{ $d->jenis_rollover }}</td>
                <td>{{ $d->kantor }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p>Mohon untuk:</p>
    <ol>
        <li>Melakukan follow-up kepada nasabah terkait deposito yang bersangkutan.</li>
        <li>Mengisi status tindak lanjut pada link, apakah:
            <ul>
                <li>Akan dicairkan</li>
                <li>Akan diperpanjang</li>
                <li>Jika ada perubahan suku bunga terbaru, mohon diupdate juga di kolom yang tersedia.</li>
            </ul>
        </li>
    </ol>

    <p>Terima kasih atas kerja samanya 🙏</p>
</body>
</html>
