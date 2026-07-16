@extends('layouts.app')

@section('title', 'Home - CERMAT Analytics')

@push('styles')
<style>
    /* Modern Container */
    .modern-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
        margin-top: 32px;
    }

    /* Welcome Banner - Image Style */
    .welcome-banner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 24px;
        padding: 0;
        margin-bottom: 48px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(102, 126, 234, 0.3);
        min-height: 280px;
        display: flex;
        align-items: center;
    }

    .welcome-banner::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M0 0h40v40H0V0zm40 40h40v40H40V40zm0-40h2l-2 2V0zm0 4l4-4h2l-6 6V4zm0 4l8-8h2L40 10V8zm0 4L52 0h2L40 14v-2zm0 4L56 0h2L40 18v-2zm0 4L60 0h2L40 22v-2zm0 4L64 0h2L40 26v-2zm0 4L68 0h2L40 30v-2zm0 4L72 0h2L40 34v-2zm0 4L76 0h2L40 38v-2zm0 4L80 0v2L42 40h-2zm4 0L80 4v2L46 40h-2zm4 0L80 8v2L50 40h-2zm4 0l28-28v2L54 40h-2zm4 0l24-24v2L58 40h-2zm4 0l20-20v2L62 40h-2zm4 0l16-16v2L66 40h-2zm4 0l12-12v2L70 40h-2zm4 0l8-8v2L74 40h-2zm4 0l4-4v2L78 40h-2z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.4;
    }

    .welcome-banner::after {
        content: '';
        position: absolute;
        bottom: -50px;
        right: -50px;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .banner-content {
        position: relative;
        z-index: 1;
        color: white;
        padding: 60px 50px;
        width: 100%;
    }

    .banner-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
        padding: 10px 20px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 20px;
        border: 1px solid rgba(255,255,255,0.3);
    }

    .banner-title {
        font-size: clamp(40px, 5vw, 64px);
        font-weight: 900;
        margin-bottom: 16px;
        letter-spacing: -1.5px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        line-height: 1.1;
    }

    .banner-subtitle {
        font-size: clamp(15px, 2vw, 18px);
        opacity: 0.95;
        max-width: 800px;
        line-height: 1.7;
        font-weight: 500;
    }

    /* Quick Actions - Card Grid */
    .actions-section {
        margin-bottom: 48px;
    }

    .section-title {
        font-size: 24px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .section-title::before {
        content: '';
        width: 4px;
        height: 28px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 4px;
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }

    .action-card {
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        padding: 28px 24px;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .action-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
    }

    .action-card:hover::before {
        transform: scaleX(1);
    }

    .action-card:hover {
        transform: translateY(-8px);
        border-color: #667eea;
        box-shadow: 0 20px 40px rgba(102, 126, 234, 0.15);
    }

    .action-card-header {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 16px;
    }

    .action-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        flex-shrink: 0;
        transition: transform 0.3s ease;
    }

    .action-card:hover .action-icon {
        transform: scale(1.1) rotate(-5deg);
    }

    .action-info {
        flex: 1;
        min-width: 0;
    }

    .action-title {
        font-size: 17px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 4px;
    }

    .action-description {
        font-size: 13px;
        color: #64748b;
        font-weight: 500;
    }

    .action-arrow {
        color: #667eea;
        font-size: 18px;
        opacity: 0;
        transform: translateX(-10px);
        transition: all 0.3s ease;
    }

    .action-card:hover .action-arrow {
        opacity: 1;
        transform: translateX(0);
    }

    /* Features Section - Premium Cards */
    .features-section {
        margin-bottom: 48px;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 24px;
    }

    .feature-card {
        background: white;
        border-radius: 20px;
        padding: 36px 32px;
        border: 1px solid #e2e8f0;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .feature-card::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        transform: translate(50%, -50%);
        transition: all 0.4s ease;
    }

    .feature-card:hover {
        transform: translateY(-12px);
        box-shadow: 0 24px 48px rgba(0, 0, 0, 0.1);
        border-color: #cbd5e1;
    }

    .feature-card:hover::after {
        width: 200px;
        height: 200px;
    }

    .feature-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 20px;
        position: relative;
        z-index: 1;
    }

    .feature-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        flex-shrink: 0;
        transition: transform 0.4s ease;
    }

    .feature-card:hover .feature-icon {
        transform: scale(1.15) rotate(10deg);
    }

    .feature-title {
        font-size: 20px;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
    }

    .feature-description {
        font-size: 14px;
        color: #64748b;
        line-height: 1.7;
        margin-bottom: 24px;
        position: relative;
        z-index: 1;
    }

    .feature-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #667eea;
        font-weight: 700;
        font-size: 14px;
        text-decoration: none;
        position: relative;
        z-index: 1;
        transition: all 0.3s ease;
    }

    .feature-link:hover {
        color: #764ba2;
        gap: 12px;
    }

    .feature-link i {
        transition: transform 0.3s ease;
    }

    .feature-link:hover i {
        transform: translateX(4px);
    }

    /* Dark Mode */
    body.dark .welcome-banner {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    }

    body.dark .action-card,
    body.dark .feature-card {
        background: #1e293b;
        border-color: #334155;
    }

    body.dark .action-title,
    body.dark .feature-title,
    body.dark .section-title {
        color: #f1f5f9;
    }

    body.dark .action-description,
    body.dark .feature-description {
        color: #94a3b8;
    }

    body.dark .action-card:hover,
    body.dark .feature-card:hover {
        background: #0f172a;
        border-color: #667eea;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .modern-container {
            margin-top: 24px;
        }

        .welcome-banner {
            border-radius: 16px;
            min-height: 220px;
        }

        .banner-content {
            padding: 40px 30px;
        }

        .banner-title {
            font-size: 32px;
        }

        .banner-subtitle {
            font-size: 14px;
        }

        .actions-grid,
        .features-grid {
            grid-template-columns: 1fr;
        }

        .section-title {
            font-size: 20px;
        }

        .feature-card {
            padding: 28px 24px;
        }
    }

    @media (max-width: 480px) {
        .action-card-header {
            flex-direction: column;
        }

        .action-icon {
            width: 48px;
            height: 48px;
            font-size: 22px;
        }
    }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="modern-container">

        <!-- Quick Actions Section -->
        <div class="actions-section">
            <h2 class="section-title">
                <i class="fas fa-bolt"></i>
                Quick Actions
            </h2>

            <div class="actions-grid">
                @can('manage cermat')
                <a href="{{ route('cermat.reports.create') }}" class="action-card">
                    <div class="action-card-header">
                        <div class="action-icon bg-light-primary text-primary">
                            <i class="fas fa-file-plus"></i>
                        </div>
                        <div class="action-info">
                            <div class="action-title">Buat Laporan</div>
                            <div class="action-description">CERMAT Report Baru</div>
                        </div>
                        <i class="fas fa-arrow-right action-arrow"></i>
                    </div>
                </a>
                @endcan

                @can('manage my action')
                <a href="{{ route('user.my-actions.index') }}" class="action-card">
                    <div class="action-card-header">
                        <div class="action-icon bg-light-warning text-warning">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="action-info">
                            <div class="action-title">My Actions</div>
                            <div class="action-description">Action Items Saya</div>
                        </div>
                        <i class="fas fa-arrow-right action-arrow"></i>
                    </div>
                </a>
                @endcan

                @can('view cermat dashboard')
                <a href="{{ route('dashboard.analytics') }}" class="action-card">
                    <div class="action-card-header">
                        <div class="action-icon bg-light-info text-info">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="action-info">
                            <div class="action-title">Dashboard</div>
                            <div class="action-description">Analytics Dashboard</div>
                        </div>
                        <i class="fas fa-arrow-right action-arrow"></i>
                    </div>
                </a>
                @endcan

                @can('manage kpi hsse')
                <a href="{{ route('kpi-hsse.index') }}" class="action-card">
                    <div class="action-card-header">
                        <div class="action-icon" style="background: #e0f2fe; color: #0284c7;">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <div class="action-info">
                            <div class="action-title">KPI HSSE</div>
                            <div class="action-description">Monitor KPI Keselamatan</div>
                        </div>
                        <i class="fas fa-arrow-right action-arrow"></i>
                    </div>
                </a>
                @endcan

                @can('manage daily checkup')
                <a href="{{ route('daily-checkup.index') }}" class="action-card">
                    <div class="action-card-header">
                        <div class="action-icon bg-light-success text-success">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <div class="action-info">
                            <div class="action-title">Daily Checkup</div>
                            <div class="action-description">Health Check Harian</div>
                        </div>
                        <i class="fas fa-arrow-right action-arrow"></i>
                    </div>
                </a>
                @endcan

                @can('manage campaign salman')
                <a href="{{ route('campaign-salman.index') }}" class="action-card">
                    <div class="action-card-header">
                        <div class="action-icon bg-light-danger text-danger">
                            <i class="fas fa-flag"></i>
                        </div>
                        <div class="action-info">
                            <div class="action-title">Campaigns Salman</div>
                            <div class="action-description">Kelola Campaign</div>
                        </div>
                        <i class="fas fa-arrow-right action-arrow"></i>
                    </div>
                </a>
                @endcan

                @can('manage broadcast')
                <a href="{{ route('broadcast.create.manual') }}" class="action-card">
                    <div class="action-card-header">
                        <div class="action-icon" style="background: #f0e6ff; color: #805dca;">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <div class="action-info">
                            <div class="action-title">Broadcast</div>
                            <div class="action-description">Kirim Pesan</div>
                        </div>
                        <i class="fas fa-arrow-right action-arrow"></i>
                    </div>
                </a>
                @endcan

                @can('manage vessels')
                <a href="{{ route('crew.index') }}" class="action-card">
                    <div class="action-card-header">
                        <div class="action-icon" style="background: #fef3c7; color: #f59e0b;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="action-info">
                            <div class="action-title">Manage Crew</div>
                            <div class="action-description">Kelola Data Crew</div>
                        </div>
                        <i class="fas fa-arrow-right action-arrow"></i>
                    </div>
                </a>
                @endcan
            </div>
        </div>

        <!-- Features Section -->
        <div class="features-section">
            <h2 class="section-title">
                <i class="fas fa-star"></i>
                Fitur Utama
            </h2>

            <div class="features-grid">
                <!-- Dashboard Analytics -->
                <div class="feature-card">
                    <div class="feature-header">
                        <div class="feature-icon bg-light-primary text-primary">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="feature-title">Dashboard Analytics</h3>
                    </div>
                    <p class="feature-description">
                        Visualisasi data real-time dengan grafik interaktif, tren laporan bulanan, dan analisis mendalam untuk monitoring performa keselamatan.
                    </p>
                    @can('view cermat dashboard')
                    <a href="{{ route('dashboard.analytics') }}" class="feature-link">
                        Buka Dashboard <i class="fas fa-arrow-right"></i>
                    </a>
                    @endcan
                </div>

                <!-- KPI HSSE -->
                <div class="feature-card">
                    <div class="feature-header">
                        <div class="feature-icon" style="background: #e0f2fe; color: #0284c7;">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <h3 class="feature-title">KPI HSSE</h3>
                    </div>
                    <p class="feature-description">
                        Monitor dan analisis Key Performance Indicators untuk Health, Safety, Security, dan Environment. Track pencapaian target dan tren performa.
                    </p>
                    @can('manage kpi hsse')
                    <a href="{{ route('kpi-hsse.index') }}" class="feature-link">
                        Lihat KPI HSSE <i class="fas fa-arrow-right"></i>
                    </a>
                    @endcan
                </div>

                <!-- Crew Management -->
                <div class="feature-card">
                    <div class="feature-header">
                        <div class="feature-icon" style="background: #fef3c7; color: #f59e0b;">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="feature-title">Crew Management</h3>
                    </div>
                    <p class="feature-description">
                        Kelola data crew, assign ke vessel, transfer antar unit, dan tracking history penempatan crew dengan sistem terintegrasi.
                    </p>
                    @can('manage crew')
                    <a href="{{ route('crew.index') }}" class="feature-link">
                        Kelola Crew <i class="fas fa-arrow-right"></i>
                    </a>
                    @endcan
                </div>

                <!-- Health Check Dashboard -->
                <div class="feature-card">
                    <div class="feature-header">
                        <div class="feature-icon bg-light-success text-success">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <h3 class="feature-title">Health Check Dashboard</h3>
                    </div>
                    <p class="feature-description">
                        Monitor kesehatan crew dan unit secara berkala dengan Daily Checkup Unit (DCU). Tracking kondisi kesehatan vessel.
                    </p>
                    @can('view dcu dashboard')
                    <a href="{{ route('dashboard.health-check') }}" class="feature-link">
                        Lihat Health Check <i class="fas fa-arrow-right"></i>
                    </a>
                    @endcan
                </div>

                <!-- Action Monitoring -->
                <div class="feature-card">
                    <div class="feature-header">
                        <div class="feature-icon bg-light-warning text-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3 class="feature-title">Action Monitoring</h3>
                    </div>
                    <p class="feature-description">
                        Monitoring tindak lanjut dari temuan keselamatan. Track progress, deadline, dan status penyelesaian action items.
                    </p>
                    @hasanyrole('super-admin|hsse')
                    <a href="{{ route('cermat.action-monitoring.index') }}" class="feature-link">
                        Monitor Actions <i class="fas fa-arrow-right"></i>
                    </a>
                    @endhasanyrole
                </div>

                <!-- CERMAT Reports -->
                <div class="feature-card">
                    <div class="feature-header">
                        <div class="feature-icon bg-light-danger text-danger">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3 class="feature-title">CERMAT Reports</h3>
                    </div>
                    <p class="feature-description">
                        Sistem pelaporan terintegrasi untuk Unsafe Act, Unsafe Condition, Security Event, dan CLSR Violation dengan workflow approval.
                    </p>
                    @can('manage cermat')
                    <a href="{{ route('cermat.reports.index') }}" class="feature-link">
                        Kelola Laporan <i class="fas fa-arrow-right"></i>
                    </a>
                    @endcan
                </div>

                <!-- Campaigns -->
                <div class="feature-card">
                    <div class="feature-header">
                        <div class="feature-icon bg-light-info text-info">
                            <i class="fas fa-flag"></i>
                        </div>
                        <h3 class="feature-title">Campaigns Salman</h3>
                    </div>
                    <p class="feature-description">
                        Kelola kampanye keselamatan, edukasi, dan awareness program. Upload materi dan monitor partisipasi.
                    </p>
                    @can('manage campaigns')
                    <a href="{{ route('campaign-salman.index') }}" class="feature-link">
                        Lihat Campaigns Salman <i class="fas fa-arrow-right"></i>
                    </a>
                    @endcan
                </div>

                <!-- Daily Checkup -->
                <div class="feature-card">
                    <div class="feature-header">
                        <div class="feature-icon" style="background: #f0e6ff; color: #805dca;">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <h3 class="feature-title">Daily Checkup</h3>
                    </div>
                    <p class="feature-description">
                        Input dan tracking pemeriksaan kesehatan harian crew. Sistem verifikasi, validasi, dan export data untuk dokumentasi.
                    </p>
                    @can('manage daily checkup')
                    <a href="{{ route('daily-checkup.index') }}" class="feature-link">
                        Input Checkup <i class="fas fa-arrow-right"></i>
                    </a>
                    @endcan
                </div>

                <!-- Broadcast System -->
                <div class="feature-card">
                    <div class="feature-header">
                        <div class="feature-icon bg-light-secondary text-secondary">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h3 class="feature-title">Broadcast System</h3>
                    </div>
                    <p class="feature-description">
                        Kirim pesan broadcast manual atau ke grup kontak untuk notifikasi, pengumuman, atau informasi penting keselamatan.
                    </p>
                    @can('manage broadcast')
                    <a href="{{ route('broadcast.create.manual') }}" class="feature-link">
                        Kirim Broadcast <i class="fas fa-arrow-right"></i>
                    </a>
                    @endcan
                </div>

                <!-- Data Master -->
                <div class="feature-card">
                    <div class="feature-header">
                        <div class="feature-icon bg-light-dark text-dark">
                            <i class="fas fa-database"></i>
                        </div>
                        <h3 class="feature-title">Data Master</h3>
                    </div>
                    <p class="feature-description">
                        Kelola master data: Companies, Vessels, Crew, Areas, Categories, dan konfigurasi sistem lainnya.
                    </p>
                    @canany(['manage companies', 'manage vessels', 'manage users'])
                    <a href="{{ route('companies.index') }}" class="feature-link">
                        Kelola Data <i class="fas fa-arrow-right"></i>
                    </a>
                    @endcanany
                </div>

                <!-- My Action Items -->
                <div class="feature-card">
                    <div class="feature-header">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="feature-title">My Action Items</h3>
                    </div>
                    <p class="feature-description">
                        Kelola action items yang ditugaskan kepada Anda. Update progress, upload bukti penyelesaian, dan request perpanjangan deadline.
                    </p>
                    @can('manage my action')
                    <a href="{{ route('user.my-actions.index') }}" class="feature-link">
                        Lihat My Actions <i class="fas fa-arrow-right"></i>
                    </a>
                    @endcan
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
