@extends('layouts.murid')

@section('title', 'Riwayat & Hasil Nilai')
@section('page_heading', 'Riwayat Ujian & Hasil Nilai KKM')

@section('content')
    <div class="page-card">
        <div style="margin-bottom:16px;">
            <h3 style="font-size:16px; font-weight:800; color:#0F172A;">Daftar Riwayat Ujian &amp; Hasil Evaluasi KKM</h3>
            <p style="font-size:12.5px; color:#64748B; margin-top:2px;">
                Nilai akhir dan status kelulusan (<strong>Lulus / Remedial</strong>) akan tampil apabila Administrator telah mengaktifkan saklar <strong>Publikasi Nilai</strong>.
            </p>
        </div>

        <div class="table-wrap">
            <table class="cbt-data-table">
                <thead>
                    <tr>
                        <th>Mata Pelajaran &amp; Sesi</th>
                        <th>Guru Pengampu</th>
                        <th>Standar KKM</th>
                        <th>Status Penyelesaian</th>
                        <th>Jawaban Benar</th>
                        <th>Nilai Akhir</th>
                        <th>Status Kelulusan</th>
                    </tr>
                </thead>
                <tbody id="tbodyRiwayatMurid"></tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/murid/riwayat.js') }}"></script>
@endpush