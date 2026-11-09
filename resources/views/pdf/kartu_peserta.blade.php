<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Peserta Ujian — {{ $profil->nama_sekolah }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #0f172a; margin: 10px; }
        .card { border: 1.5px solid #1e3a8a; border-radius: 6px; padding: 12px; margin-bottom: 16px; page-break-inside: avoid; }
        .header { border-bottom: 2px solid #2563eb; padding-bottom: 6px; margin-bottom: 10px; text-align: center; }
        .header h3 { margin: 0; font-size: 14px; color: #1e3a8a; text-transform: uppercase; }
        .header p { margin: 2px 0 0; font-size: 10px; color: #475569; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 4px 6px; vertical-align: top; }
        .label { font-weight: bold; width: 130px; color: #334155; }
        .cred-box { background: #eff6ff; border: 1px dashed #2563eb; padding: 6px; margin-top: 8px; border-radius: 4px; }
    </style>
</head>
<body>
    @foreach($daftarMurid as $murid)
    <div class="card">
        <div class="header">
            <h3>KARTU PESERTA UJIAN BERBASIS KOMPUTER (CBT)</h3>
            <p><strong>{{ $profil->nama_sekolah }}</strong> (NPSN: {{ $profil->npsn }}) — TA {{ $profil->tahun_ajaran }} ({{ $profil->semester }})</p>
        </div>
        <table>
            <tr>
                <td class="label">Nama Lengkap Peserta</td>
                <td>: <strong>{{ $murid->nama_lengkap }}</strong></td>
            </tr>
            <tr>
                <td class="label">Nomor Induk Siswa (NIS)</td>
                <td>: {{ $murid->nis }}</td>
            </tr>
            <tr>
                <td class="label">Kelas / Kompetensi Keahlian</td>
                <td>: {{ $murid->kelas?->nama_kelas }} — {{ $murid->kelas?->jurusan?->nama_jurusan }}</td>
            </tr>
        </table>
        <div class="cred-box">
            <strong>Kredensial Login Sistem CBT (Single Device Lock):</strong><br>
            Username / NIS : <strong>{{ $murid->nis }}</strong> &nbsp;|&nbsp;
            Password Cetak : <strong>{{ $murid->password_cetak ?? $murid->nis }}</strong>
        </div>
    </div>
    @endforeach
</body>
</html>