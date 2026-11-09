<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Hadir & Berita Acara — {{ $sesi->nama_sesi_ujian }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #0f172a; }
        .kop { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 8px; margin-bottom: 14px; }
        .kop h2 { margin: 0; font-size: 16px; text-transform: uppercase; color: #1e3a8a; }
        .kop p { margin: 3px 0 0; font-size: 10px; color: #334155; }
        .meta-table { width: 100%; margin-bottom: 12px; }
        .meta-table td { padding: 3px 4px; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .data-table th, .data-table td { border: 1px solid #334155; padding: 6px; text-align: left; }
        .data-table th { background: #e2e8f0; font-size: 10px; text-transform: uppercase; }
        .berita-acara { margin-top: 16px; border: 1px solid #475569; padding: 10px; }
        .ttd { margin-top: 24px; width: 100%; }
        .ttd td { text-align: center; width: 50%; }
    </style>
</head>
<body>
    <div class="kop">
        <h2>{{ $profil->nama_sekolah }}</h2>
        <p>NPSN: {{ $profil->npsn }} | {{ $profil->alamat_sekolah }}</p>
        <p><strong>DAFTAR HADIR & BERITA ACARA PELAKSANAAN UJIAN CBT — TA {{ $profil->tahun_ajaran }} ({{ $profil->semester }})</strong></p>
    </div>

    <table class="meta-table">
        <tr>
            <td width="18%"><strong>Nama Sesi Ujian</strong></td>
            <td width="35%">: {{ $sesi->nama_sesi_ujian }}</td>
            <td width="18%"><strong>Tanggal / Waktu</strong></td>
            <td>: {{ $sesi->tanggal_ujian->format('d-m-Y') }} ({{ $sesi->jam_mulai }} - {{ $sesi->jam_selesai }})</td>
        </tr>
        <tr>
            <td><strong>Mata Pelajaran</strong></td>
            <td>: {{ $sesi->plotting->mapel->nama_mapel }} (KKM: {{ $sesi->plotting->mapel->nilai_kkm }})</td>
            <td><strong>Kelas / Rombel</strong></td>
            <td>: {{ $sesi->plotting->kelas->nama_kelas }}</td>
        </tr>
        <tr>
            <td><strong>Guru Pengampu</strong></td>
            <td>: {{ $sesi->plotting->guru->nama_lengkap }}</td>
            <td><strong>Pengawas Ruang</strong></td>
            <td>: {{ $sesi->nama_pengawas_ruang ?? $sesi->plotting->guru->nama_lengkap }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="18%">NIS</th>
                <th width="35%">Nama Lengkap Murid</th>
                <th width="18%">Status Kehadiran CBT</th>
                <th width="12%">Pelanggaran</th>
                <th width="12%">Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($semuaMuridKelas as $idx => $m)
                @php
                    $ujian = $ujianPerMurid->get($m->id_murid);
                    $hadir = $ujian && $ujian->status_ujian !== 'belum_mulai';
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $m->nis }}</td>
                    <td>{{ $m->nama_lengkap }}</td>
                    <td>{{ $hadir ? 'Hadir (' . $ujian->status_ujian . ')' : 'Belum Hadir / Alfa' }}</td>
                    <td>{{ $ujian ? $ujian->jumlah_pelanggaran . 'x' : '0x' }}</td>
                    <td>{{ $idx + 1 }}. ............</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="berita-acara">
        <strong>Catatan Berita Acara Pelaksanaan Ujian:</strong><br>
        {{ $sesi->catatan_berita_acara ?? 'Pelaksanaan ujian berjalan tertib sesuai pakta integritas CBT.' }}
    </div>

    <table class="ttd">
        <tr>
            <td>
                Mengetahui,<br>Kepala {{ $profil->nama_sekolah }}
                <br><br><br><br>
                <strong><u>{{ $profil->nama_kepala_sekolah }}</u></strong>
            </td>
            <td>
                Kota Bekasi, {{ now()->format('d F Y') }}<br>Pengawas Ruang / Guru Pengampu
                <br><br><br><br>
                <strong><u>{{ $sesi->nama_pengawas_ruang ?? $sesi->plotting->guru->nama_lengkap }}</u></strong>
            </td>
        </tr>
    </table>
</body>
</html>