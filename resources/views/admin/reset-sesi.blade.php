@extends('layouts.admin')

@section('title', 'Reset Sesi Terkunci')
@section('page_heading', 'Reset Sesi Terkunci (Single Device Lock)')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/pages.css') }}">
@endpush

@section('content')
    <div class="page-card">
        <div class="page-card-header">
            <div>
                <h3>Manajemen Reset Sesi Login Terkunci (Single Device Lock)</h3>
                <p>Membuka kunci login perangkat siswa tanpa menghapus jawaban tersimpan atau sisa waktu ujian</p>
            </div>
            <button class="cbt-btn outline" onclick="muatSesiTerkunci()">Refresh Daftar</button>
        </div>

        <div class="table-wrap">
            <table class="cbt-data-table">
                <thead>
                    <tr>
                        <th>NIS</th>
                        <th>Nama Lengkap Murid</th>
                        <th>Kelas</th>
                        <th>Waktu Login Terakhir</th>
                        <th>Status Perangkat</th>
                        <th>Aksi Reset</th>
                    </tr>
                </thead>
                <tbody id="tbodyResetSesi"></tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/admin/reset.js') }}"></script>
@endpush