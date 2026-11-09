@extends('layouts.admin')

@section('title', 'Pengaturan Akun Administrator')
@section('page_heading', 'Profil & Keamanan Administrator')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/pages.css') }}">
@endpush

@section('content')
    <div class="page-card" style="max-width: 620px;">
        <div class="page-card-header">
            <div>
                <h3>Pengaturan Profil &amp; Kredensial Administrator</h3>
                <p>Ubah nama pengguna (username) dan kata sandi login Portal Admin CBT</p>
            </div>
        </div>

        <form id="formProfilAdmin" onsubmit="simpanProfilAdmin(event)" style="display:flex; flex-direction:column; gap:16px;">
            <div class="form-field-group">
                <label>Username / Nama Login Administrator</label>
                <input type="text" id="adminUsernameInput" class="cbt-input-sm" required placeholder="Contoh: admin_smkn1bks">
            </div>

            <div class="form-field-group">
                <label>Login Terakhir Tercatat</label>
                <input type="text" id="adminLastLoginInput" class="cbt-input-sm font-mono" readonly style="background:#F8FAFC; color:#64748B;">
            </div>

            <div class="form-field-group">
                <label>Password Baru (Kosongkan jika tidak ingin mengganti password)</label>
                <div class="password-gen-row">
                    <input type="text" id="adminNewPassword" class="cbt-input-sm font-mono" placeholder="Minimal 6 karakter">
                    <button type="button" class="cbt-btn edit-btn" onclick="generatePassAdmin()">Generate Password Kuat</button>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; padding-top:8px;">
                <button type="submit" class="cbt-btn primary">Simpan Perubahan Profil</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/admin/profil.js') }}"></script>
@endpush