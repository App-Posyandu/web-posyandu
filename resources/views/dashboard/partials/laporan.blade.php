{{-- layout excel --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Pengajuan</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2>Laporan Pengajuan</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Pengaju</th>
                <th>Tanggal Pengajuan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pengajuans as $pengajuan)
            <tr>
                <td>{{ $pengajuan->id }}</td>
                <td>{{ $pengajuan->nama_pengaju }}</td>
                <td>{{ $pengajuan->tanggal_pengajuan }}</td>
                <td>{{ $pengajuan->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>