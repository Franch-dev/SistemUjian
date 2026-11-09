<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMK Negeri 1 Kota Bekasi — Sistem Ujian Online</title>

    <!-- Pemanggilan CSS Global & CSS Khusus Halaman Login -->
    <link rel="stylesheet" href="{{ asset('assets/css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/auth/login.css') }}">
</head>
<body>

    <div class="login-page-wrapper">
        <div class="login-main-card">

            <!-- 1. TOP HEADER BAR (TAMPIL DI DESKTOP) -->
            <header class="login-top-bar">
                <div class="brand-identity">
                    <div class="school-logo-circle">
                        <!-- Ikon Emblem Sekolah -->
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1D5DEC" stroke-width="1.8">
                            <path d="M12 2L3 7l9 5 9-5-9-5z"/>
                            <path d="M3 17l9 5 9-5"/>
                            <path d="M3 12l9 5 9-5"/>
                        </svg>
                    </div>
                    <div class="brand-text">
                        <h1>SMK Negeri 1 Kota Bekasi</h1>
                        <span>SISTEM UJIAN ONLINE</span>
                    </div>
                </div>

                <div class="top-tagline">
                    <span>Berbasis Teknologi, Menuju Pendidikan yang Lebih Baik</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1D5DEC" stroke-width="2">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                </div>
            </header>

            <!-- 2. HEADER AWAN KHUSUS TAMPILAN MOBILE / HP -->
            <div class="mobile-cloud-header">
                <div class="school-logo-circle large">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#1D5DEC" stroke-width="1.8">
                        <path d="M12 2L3 7l9 5 9-5-9-5z"/>
                        <path d="M3 17l9 5 9-5"/>
                        <path d="M3 12l9 5 9-5"/>
                    </svg>
                </div>
                <h2>SMK NEGERI 1 KOTA BEKASI</h2>
                <p>Sistem Ujian Online</p>
            </div>

            <!-- 3. KONTEN UTAMA (KIRI HERO DESKTOP + KANAN FORM LOGIN) -->
            <div class="login-content-grid">

                <!-- KOLOM KIRI: HERO SAMBUTAN & ILUSTRASI (DESKTOP) -->
                <section class="login-left-hero">
                    <div>
                        <div class="hero-Heading-group">
                            <h2>Selamat Datang di</h2>
                            <h3>Sistem Ujian Online</h3>
                            <h4>SMK Negeri 1 Kota Bekasi</h4>
                        </div>

                        <p class="hero-description">
                            Lakukan ujian dengan mudah, aman, dan nyaman kapan saja dan di mana saja.
                            Dilengkapi dengan fitur pengawasan dan keamanan terbaik.
                        </p>

                        <!-- 4 Kartu Keunggulan -->
                        <div class="feature-cards-row">
                            <div class="feature-pill-card">
                                <div class="feature-icon-box blue">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                        <path d="M9 12l2 2 4-4"/>
                                    </svg>
                                </div>
                                <p>Aman &amp; Terpercaya</p>
                            </div>

                            <div class="feature-pill-card">
                                <div class="feature-icon-box mint">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="9"/>
                                        <path d="M12 7v5l3 3"/>
                                    </svg>
                                </div>
                                <p>Mudah Digunakan</p>
                            </div>

                            <div class="feature-pill-card">
                                <div class="feature-icon-box purple">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="12" rx="2"/>
                                        <path d="M8 20h8M12 16v4"/>
                                    </svg>
                                </div>
                                <p>Akses Kapan Saja</p>
                            </div>

                            <div class="feature-pill-card amber-border">
                                <div class="feature-icon-box amber">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="5" y="11" width="14" height="10" rx="2"/>
                                        <path d="M8 11V7a4 4 0 118 0v4"/>
                                    </svg>
                                </div>
                                <p>Pengawasan Ketat</p>
                            </div>
                        </div>
                    </div>

                    <!-- Ilustrasi Buku + Tablet Ujian + Tempat Pensil -->
                    <div>
                        <div class="hero-illustration-stage">
                            <!-- Tumpukan 3 Buku -->
                            <div class="books-stack">
                                <div class="book-item book-1"><span>Masa Depan</span></div>
                                <div class="book-item book-2"><span>Berprestasi</span></div>
                                <div class="book-item book-3"><span>Belajar</span></div>
                            </div>

                            <!-- Layar Tablet Ujian Online -->
                            <div class="mockup-tablet-frame">
                                <div class="mockup-tablet-screen">
                                    <div class="tablet-top">
                                        <span class="tablet-title">
                                            <span class="dot-blue"></span> Ujian Online
                                        </span>
                                        <span class="tablet-timer font-mono">● 45:20</span>
                                    </div>
                                    <div class="tablet-q-header">
                                        <strong>Soal 1 dari 20</strong>
                                        <span class="tablet-badge-active">Aktif</span>
                                    </div>
                                    <div class="tablet-line-skeleton"></div>
                                    <div class="tablet-line-skeleton short"></div>

                                    <div class="tablet-option-row selected">
                                        <span class="radio-dot checked"></span>
                                        <span>Pilihan Jawaban A</span>
                                    </div>
                                    <div class="tablet-option-row">
                                        <span class="radio-dot"></span>
                                        <span>Pilihan Jawaban B</span>
                                    </div>
                                    <div class="tablet-option-row">
                                        <span class="radio-dot"></span>
                                        <span>Pilihan Jawaban C</span>
                                    </div>

                                    <div class="tablet-bottom-action">
                                        <span class="tablet-next-btn">Next →</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Tempat Pensil -->
                            <div class="pencil-cup">
                                <div class="pencil-sticks">
                                    <span class="stick green"></span>
                                    <span class="stick blue"></span>
                                    <span class="stick orange"></span>
                                </div>
                            </div>
                        </div>

                        <div class="hero-footer-note">
                            SMK Negeri 1 Kota Bekasi — <a href="#">Ujian Online, Disiplin, Jujur, Raih Masa Depan</a>
                        </div>
                    </div>
                </section>

                <!-- KOLOM KANAN: FORM LOGIN (DESKTOP & MOBILE) -->
                <section class="login-right-form-panel">

                    <!-- Sambutan Khusus Tampilan Mobile/HP -->
                    <div class="mobile-welcome-banner">
                        <span>Selamat Datang di</span>
                        <h1>Sistem Ujian Online</h1>
                        <h3>SMK Negeri 1 Kota Bekasi</h3>
                        <p>Lakukan ujian dengan mudah, aman, dan nyaman kapan saja dan di mana saja.</p>
                    </div>

                    <!-- Logo & Identitas Tengah (Desktop) -->
                    <div class="form-brand-center">
                        <div class="school-logo-circle large">
                            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#1D5DEC" stroke-width="1.8">
                                <path d="M12 2L3 7l9 5 9-5-9-5z"/>
                                <path d="M3 17l9 5 9-5"/>
                                <path d="M3 12l9 5 9-5"/>
                            </svg>
                        </div>
                        <h3>SMK Negeri 1 Kota Bekasi</h3>
                        <p>Sistem Ujian Online</p>
                    </div>

                    <div class="welcome-back-title">
                        <h2>Selamat Datang Kembali</h2>
                        <p>Silakan masuk ke akun Anda untuk melanjutkan ke sistem ujian.</p>
                    </div>

                    <!-- Kotak Pesan Alert / Single Device Lock -->
                    <div id="loginAlertBox" class="cbt-alert"></div>

                    <!-- Form Input Login -->
                    <form id="cbtLoginForm" autocomplete="off">
                        <!-- Input Username atau NIS -->
                        <div class="input-group-field">
                            <span class="input-icon-left">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            </span>
                            <input
                                type="text"
                                id="identifierInput"
                                class="cbt-input"
                                placeholder="Username atau NIS"
                                required
                            >
                        </div>

                        <!-- Input Password -->
                        <div class="input-group-field">
                            <span class="input-icon-left">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="5" y="11" width="14" height="10" rx="2"/>
                                    <path d="M8 11V7a4 4 0 1 1 8 0v4"/>
                                </svg>
                            </span>
                            <input
                                type="password"
                                id="passwordInput"
                                class="cbt-input"
                                placeholder="Password"
                                required
                            >
                            <button type="button" id="togglePasswordBtn" class="password-toggle-btn" title="Tampilkan Password">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Tombol Masuk & Hubungi Admin -->
                        <div class="form-actions-stack">
                            <button type="submit" id="submitLoginBtn" class="btn-primary">
                                <span>→ Masuk</span>
                            </button>

                            <button type="button" id="contactAdminBtn" class="btn-secondary-outline">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                </svg>
                                <span>Hubungi Admin</span>
                            </button>
                        </div>
                    </form>

                </section>
            </div>

            <!-- 4. FOOTER BAWAH KHUSUS TAMPILAN MOBILE / HP -->
            <footer class="mobile-wave-footer">
                <span>Disiplin • Jujur • Berprestasi</span>
            </footer>

        </div>
    </div>

    <!-- Pemanggilan File JavaScript Khusus Halaman Login -->
    <script src="{{ asset('assets/js/auth/login.js') }}"></script>
</body>
</html>