<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\ProfilSekolah;
use App\Models\Admin;
use App\Models\MasterJurusan;
use App\Models\MasterKelas;
use App\Models\Guru;
use App\Models\Murid;
use App\Models\SesiLoginMurid;
use App\Models\MasterMapel;
use App\Models\PlottingMapel;
use App\Models\BankSoal;
use App\Models\SesiUjian;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Profil Sekolah
        $profil = ProfilSekolah::create([
            'nama_sekolah'        => 'SMK Negeri 1 Kota Bekasi',
            'npsn'                => '20223136',
            'logo_path'           => null,
            'tahun_ajaran'        => '2025/2026',
            'semester'            => 'Ganjil',
            'nama_kepala_sekolah' => 'Drs. H. Sugiyono, M.M.',
            'alamat_sekolah'      => 'Jl. Bintara VIII No. 2, RT.005/RW.003, Bintara, Kec. Bekasi Barat, Kota Bekasi, Jawa Barat 17134',
        ]);

        // 2. Akun Admin Utama
        $admin = Admin::create([
            'username'       => 'admin_smkn1bks',
            'password_hash'  => Hash::make('admin123'),
            'login_terakhir' => null,
        ]);

        // 3. Master Jurusan
        $jurusanRpl = MasterJurusan::create([
            'kode_jurusan' => 'RPL',
            'nama_jurusan' => 'Rekayasa Perangkat Lunak',
        ]);

        $jurusanTkj = MasterJurusan::create([
            'kode_jurusan' => 'TKJ',
            'nama_jurusan' => 'Teknik Komputer dan Jaringan',
        ]);

        // 4. Master Kelas
        $kelasXiiRpl1 = MasterKelas::create([
            'id_jurusan'  => $jurusanRpl->id_jurusan,
            'tingkat'     => 'XII',
            'kode_rombel' => '1',
            'nama_kelas'  => 'XII RPL 1',
        ]);

        $kelasXiRpl2 = MasterKelas::create([
            'id_jurusan'  => $jurusanRpl->id_jurusan,
            'tingkat'     => 'XI',
            'kode_rombel' => '2',
            'nama_kelas'  => 'XI RPL 2',
        ]);

        // 5. Akun Guru Pengampu
        $guru = Guru::create([
            'kode_guru'     => 'BUDISA4821', // Format: 6 huruf nama + 4 digit acak
            'nama_lengkap'  => 'Budi Santoso, S.Kom.',
            'nip'           => '198503122010011012',
            'password_hash' => Hash::make('guru123456'),
        ]);

        // 6. Akun Murid Contoh
        $murid1 = Murid::create([
            'id_kelas'        => $kelasXiiRpl1->id_kelas,
            'nis'             => '2022313601',
            'nama_lengkap'    => 'Ahmad Rizky Pratama',
            'password_hash'   => Hash::make('2022313601'),
            'password_cetak'  => '2022313601',
        ]);

        // Inisialisasi baris tabel Single Device Lock untuk Murid (State default: Terbuka)
        SesiLoginMurid::create([
            'id_murid'        => $murid1->id_murid,
            'session_token'   => null,
            'is_login_locked' => false,
            'waktu_login'     => null,
        ]);

        // Murid 2 (Contoh murid terkunci untuk simulasi)
        $murid2 = Murid::create([
            'id_kelas'        => $kelasXiiRpl1->id_kelas,
            'nis'             => '2022313619',
            'nama_lengkap'    => 'Nabila Putri Maharani',
            'password_hash'   => Hash::make('2022313619'),
            'password_cetak'  => '2022313619',
        ]);

        SesiLoginMurid::create([
            'id_murid'        => $murid2->id_murid,
            'session_token'   => 'token_dummy_perangkat_lama_123',
            'is_login_locked' => true, // Terkunci di perangkat lain
            'waktu_login'     => now()->subMinutes(25),
        ]);

        // 7. Master Mapel
        $mapelBasisData = MasterMapel::create([
            'kode_mapel' => 'MAPEL-BD-01',
            'nama_mapel' => 'Basis Data',
            'nilai_kkm'  => 75,
        ]);

        $mapelPbo = MasterMapel::create([
            'kode_mapel' => 'MAPEL-PBO-02',
            'nama_mapel' => 'Pemrograman Berorientasi Objek',
            'nilai_kkm'  => 78,
        ]);

        // 8. Plotting Guru Pengampu (Guru Budi -> Basis Data -> XII RPL 1)
        $plotting = PlottingMapel::create([
            'id_guru'  => $guru->id_guru,
            'id_mapel' => $mapelBasisData->id_mapel,
            'id_kelas' => $kelasXiiRpl1->id_kelas,
        ]);

        // 9. Contoh Butir Bank Soal PG (A-E)
        BankSoal::create([
            'id_plotting'   => $plotting->id_plotting,
            'pertanyaan'    => 'Perintah SQL manakah yang digunakan untuk mengambil data dari tabel murid dengan menggabungkan tabel kelas berdasarkan id_kelas?',
            'path_gambar'   => null,
            'opsi_a'        => 'SELECT * FROM murid MERGE master_kelas ON murid.id_kelas = master_kelas.id_kelas',
            'opsi_b'        => 'SELECT * FROM murid INNER JOIN master_kelas ON murid.id_kelas = master_kelas.id_kelas',
            'opsi_c'        => 'SELECT ALL FROM murid CONNECT master_kelas WITH id_kelas',
            'opsi_d'        => 'GET DATA FROM murid, master_kelas WHERE id_kelas IS EQUAL',
            'opsi_e'        => 'JOIN murid WITH master_kelas BY id_kelas',
            'kunci_jawaban' => 'B',
            'bobot_soal'    => 1,
        ]);

        BankSoal::create([
            'id_plotting'   => $plotting->id_plotting,
            'pertanyaan'    => 'Kunci utama (Primary Key) yang terbentuk dari gabungan dua atau lebih kolom Foreign Key dalam sebuah tabel relasi disebut...',
            'path_gambar'   => null,
            'opsi_a'        => 'Candidate Key',
            'opsi_b'        => 'Foreign Index',
            'opsi_c'        => 'Composite Primary Key',
            'opsi_d'        => 'Alternate Key',
            'opsi_e'        => 'Surrogate Key',
            'kunci_jawaban' => 'C',
            'bobot_soal'    => 1,
        ]);

        // 10. Sesi Ujian Aktif
        SesiUjian::create([
            'id_plotting'                => $plotting->id_plotting,
            'id_admin'                   => $admin->id_admin,
            'id_profil'                  => $profil->id_profil,
            'nama_sesi_ujian'            => 'UAS Ganjil — Basis Data XII RPL 1',
            'tanggal_ujian'              => now()->toDateString(),
            'jam_mulai'                  => '08:00:00',
            'jam_selesai'                => '12:00:00',
            'durasi_menit'               => 90,
            'batas_toleransi_anticheat'  => 3,
            'status_sesi'                => 'terbuka',
            'visibilitas_jadwal'         => 'tampil',
            'publikasi_nilai'            => false,
            'nama_pengawas_ruang'        => 'Budi Santoso, S.Kom.',
            'catatan_berita_acara'       => 'Pelaksanaan UAS Ganjil Lab RPL.',
        ]);
    }
}