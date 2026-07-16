
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Verifikasi Sertifikat Kru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
            
            --primary: #1e40af;
            --primary-light: #eff6ff;
            --primary-hover: #1e3a8a;
            
            --emerald-500: #10b981;
            --emerald-600: #059669;
            --emerald-700: #047857;
            --emerald-50: #ecfdf5;
            
            --amber-500: #f59e0b;
            --amber-600: #d97406;
            --amber-700: #b45309;
            --amber-50: #fffbeb;
            
            --rose-500: #f43f5e;
            --rose-600: #e11d48;
            --rose-700: #be123c;
            --rose-50: #fff1f2;
            
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        * { 
            box-sizing: border-box; 
        }

        body {
            background-color: var(--slate-50);
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            color: var(--slate-800);
        }

        /* ── Header ── */
        .site-header {
            background: linear-gradient(135deg, var(--slate-900) 0%, #0f2d56 100%);
            padding: 2rem 0;
            margin-bottom: 2.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }
        .site-header::before {
            content: '';
            position: absolute;
            inset: 0;
            opacity: 0.06;
            background-image: radial-gradient(var(--slate-400) 1px, transparent 1px);
            background-size: 16px 16px;
            pointer-events: none;
        }
        .site-header h1 {
            color: #fff;
            font-size: 1.35rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.3;
        }
        .site-header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
            margin: 6px 0 0;
        }
        .site-header .logo-badge {
            width: 48px; 
            height: 48px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            display: flex; 
            align-items: center; 
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn-back-login {
            border-color: rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.05);
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.55rem 1.1rem;
            border-radius: 10px;
            color: #fff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-back-login:hover {
            border-color: #fff;
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            transform: translateY(-1px);
        }

        /* ── Panels ── */
        .panel {
            background: #fff;
            border: 1px solid var(--slate-200);
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: box-shadow 0.3s, transform 0.3s;
        }
        .panel:hover {
            box-shadow: var(--shadow-md);
        }
        .panel-body { 
            padding: 2rem; 
        }

        .panel-header-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--slate-800);
            margin-bottom: 1.5rem;
            border-bottom: 1.5px solid var(--slate-100);
            padding-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── Form inputs ── */
        .form-group-custom {
            position: relative;
            margin-bottom: 1.25rem;
        }
        .f-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--slate-500);
            margin-bottom: 6px;
            display: block;
        }
        .form-control-custom {
            height: 46px;
            border-radius: 10px;
            border: 1.5px solid var(--slate-200);
            padding-left: 2.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--slate-800);
            background-color: var(--slate-50);
            transition: all 0.2s ease-in-out;
        }
        .form-control-custom:focus {
            background-color: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.1);
            outline: 0;
        }
        .form-control-custom.is-invalid {
            border-color: var(--rose-500);
            background-color: var(--rose-50);
        }
        .form-icon-prefix {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--slate-400);
            font-size: 1.05rem;
            pointer-events: none;
            transition: color 0.2s;
        }
        .form-control-custom:focus ~ .form-icon-prefix {
            color: var(--primary);
        }

        /* ── Divider ── */
        .or-divider {
            display: flex; 
            align-items: center; 
            gap: 12px;
            color: var(--slate-400); 
            font-size: .7rem; 
            font-weight: 700;
            letter-spacing: .06em;
            margin: 1.25rem 0;
            text-transform: uppercase;
        }
        .or-divider::before, .or-divider::after {
            content: ''; 
            flex: 1; 
            height: 1.5px; 
            background: var(--slate-200);
        }

        /* ── Captcha ── */
        .captcha-box-custom {
            background: linear-gradient(135deg, var(--slate-900) 0%, #0f2d56 100%);
            border: 1px solid var(--slate-800);
            border-radius: 10px;
            padding: 0.85rem 1.1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.85rem;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.15);
        }
        .captcha-q-custom {
            font-size: 1.5rem;
            font-weight: 800;
            font-family: 'Courier New', Courier, monospace;
            color: #38bdf8;
            letter-spacing: 0.05em;
            text-shadow: 0 0 8px rgba(56, 189, 248, 0.25);
        }
        .captcha-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            text-align: right;
        }
        .captcha-info-label {
            font-size: 0.65rem;
            font-weight: 700;
            color: var(--slate-400);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .captcha-info-desc {
            font-size: 0.72rem;
            color: var(--slate-300);
        }

        /* ── Action Buttons ── */
        .btn-verify-custom {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #fff;
            border: none;
            height: 48px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.925rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.15), 0 2px 4px -2px rgba(37, 99, 235, 0.15);
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .btn-verify-custom:hover:not(:disabled) {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-1px);
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.2);
        }
        .btn-verify-custom:active:not(:disabled) {
            transform: translateY(1px);
        }
        .btn-verify-custom:disabled {
            opacity: 0.75;
            cursor: not-allowed;
        }

        /* ── Loading Overlay ── */
        .result-wrap { 
            position: relative; 
            min-height: 420px; 
        }
        .loading-overlay-custom {
            position: absolute; 
            inset: 0;
            background: rgba(255, 255, 255, 0.88);
            border-radius: 16px;
            display: flex; 
            flex-direction: column;
            align-items: center; 
            justify-content: center; 
            gap: 12px;
            z-index: 20;
            backdrop-filter: blur(4px);
        }
        .loading-overlay-custom .spinner-border {
            width: 42px; 
            height: 42px;
            color: var(--primary);
            border-width: 3.5px;
        }
        .loading-overlay-custom p { 
            font-size: 0.875rem; 
            font-weight: 600;
            color: var(--slate-600); 
            margin: 0; 
        }

        /* ── Empty & Error States ── */
        .empty-state-custom {
            display: flex; 
            flex-direction: column;
            align-items: center; 
            justify-content: center;
            min-height: 380px; 
            text-align: center;
            padding: 3rem 2rem;
        }
        .empty-state-icon-wrapper {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            background-color: var(--slate-100);
            color: var(--slate-400);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1.25rem;
            position: relative;
        }
        .empty-state-icon-wrapper::after {
            content: '';
            position: absolute;
            inset: -7px;
            border-radius: 50%;
            border: 1.5px dashed var(--slate-300);
            animation: rotate-dashed 25s linear infinite;
        }
        @keyframes rotate-dashed {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .empty-state-custom h5 {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--slate-700);
            margin-bottom: 0.5rem;
        }
        .empty-state-custom p {
            font-size: 0.825rem;
            color: var(--slate-500);
            max-width: 310px;
            margin: 0;
            line-height: 1.5;
        }

        .notfound-state-icon-wrapper {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            background-color: var(--rose-50);
            color: var(--rose-500);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1.25rem;
            position: relative;
        }
        .notfound-state-icon-wrapper::after {
            content: '';
            position: absolute;
            inset: -7px;
            border-radius: 50%;
            border: 1.5px dashed var(--rose-200);
        }

        /* ── Result display styling ── */
        .result-summary-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            padding: 0.75rem 1.25rem;
            background-color: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: 10px;
        }
        .result-summary-text {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--slate-700);
        }

        /* ── Cert card premium ── */
        .cert-card-premium {
            background: #fff;
            border: 1px solid var(--slate-200);
            border-radius: 14px;
            padding: 1.5rem;
            margin-bottom: 1.25rem;
            box-shadow: var(--shadow-sm);
            border-left: 5px solid var(--slate-300);
            position: relative;
            transition: all 0.25s ease;
            animation: fadeSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .cert-card-premium:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: none; }
        }

        .cert-card-premium.lulus { border-left-color: var(--emerald-500); }
        .cert-card-premium.pending { border-left-color: var(--amber-500); }
        .cert-card-premium.tidak-lulus { border-left-color: var(--rose-500); }

        /* Initials Avatar Badge */
        .initials-avatar-badge {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            background-color: var(--slate-100);
            color: var(--slate-600);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
            border: 1px solid var(--slate-200);
            flex-shrink: 0;
        }
        .cert-card-premium.lulus .initials-avatar-badge {
            background-color: var(--emerald-50);
            color: var(--emerald-700);
            border-color: rgba(16, 185, 129, 0.25);
        }
        .cert-card-premium.pending .initials-avatar-badge {
            background-color: var(--amber-50);
            color: var(--amber-700);
            border-color: rgba(245, 158, 11, 0.25);
        }
        .cert-card-premium.tidak-lulus .initials-avatar-badge {
            background-color: var(--rose-50);
            color: var(--rose-700);
            border-color: rgba(244, 63, 94, 0.25);
        }

        .cert-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--slate-900);
        }
        .cert-position-badge {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--slate-500);
        }

        .status-badge-custom {
            font-size: 0.725rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 0.35rem 0.8rem;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .status-badge-custom.lulus {
            background-color: var(--emerald-50);
            color: var(--emerald-700);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        .status-badge-custom.pending {
            background-color: var(--amber-50);
            color: var(--amber-700);
            border: 1px solid rgba(245, 158, 11, 0.2);
        }
        .status-badge-custom.tidak-lulus {
            background-color: var(--rose-50);
            color: var(--rose-700);
            border: 1px solid rgba(244, 63, 94, 0.2);
        }

        /* Metadata Grid */
        .cert-meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px 20px;
            margin-top: 1rem;
            padding-top: 0.5rem;
        }
        .meta-item-custom {
            display: flex;
            flex-direction: column;
        }
        .meta-item-custom.col-span-2 {
            grid-column: span 2;
        }
        .meta-label-custom {
            font-size: 0.65rem;
            font-weight: 700;
            color: var(--slate-400);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 2px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .meta-val-custom {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--slate-800);
        }

        /* validity */
        .validity-block-custom {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: 8px;
            padding: 0.45rem 0.85rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--slate-600);
            margin-top: 1.25rem;
        }

        .notes-block-custom {
            background-color: #fafafa;
            border-left: 3px solid var(--slate-200);
            border-radius: 0 8px 8px 0;
            padding: 0.75rem 1rem;
            font-size: 0.8rem;
            color: var(--slate-600);
            margin-top: 1rem;
            line-height: 1.45;
        }

        /* Alerts */
        .alert-custom {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            animation: alertFadeDown 0.25s ease-out;
        }
        @keyframes alertFadeDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .alert-custom-danger {
            background-color: var(--rose-50);
            border: 1px solid rgba(244, 63, 94, 0.2);
            color: var(--rose-700);
        }
        .alert-custom-warning {
            background-color: var(--amber-50);
            border: 1px solid rgba(245, 158, 11, 0.2);
            color: var(--amber-700);
        }

        .security-note {
            font-size: 0.725rem; 
            color: var(--slate-400);
            margin-top: 1.25rem; 
            line-height: 1.5;
            text-align: center;
        }

        @media (max-width: 991px) {
            .site-header {
                padding: 1.5rem 0;
                margin-bottom: 1.75rem;
            }
            .site-header h1 {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 575px) {
            .cert-meta-grid { 
                grid-template-columns: 1fr; 
                gap: 10px;
            }
            .meta-item-custom.col-span-2 {
                grid-column: span 1;
            }
            .panel-body { 
                padding: 1.25rem; 
            }
        }
    </style>
</head>
<body>

<header class="site-header">
    <div class="container">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="logo-badge">
                    <i class="bi bi-patch-check-fill text-info" style="font-size:1.4rem"></i>
                </div>
                <div>
                    <h1>Verifikasi Sertifikat Kru <span style="font-weight:300; opacity:.75; font-size: 0.9em;" class="d-block d-md-inline ms-0 ms-md-2">/ Seafarer Verification</span></h1>
                    <p>Portal resmi untuk melakukan verifikasi keaslian sertifikat assessment crew</p>
                </div>
            </div>
            <a href="<?php echo e(route('login')); ?>" class="btn-back-login">
                <i class="bi bi-box-arrow-in-right"></i> Masuk Aplikasi
            </a>
        </div>
    </div>
</header>

<div class="container pb-5">
    <div class="row g-4 align-items-start justify-content-center">

        
        <div class="col-lg-5 col-xl-4">
            <div class="panel">
                <div class="panel-body">
                    <div class="panel-header-title">
                        <i class="bi bi-search text-primary"></i>
                        <span>Pencarian Sertifikat</span>
                    </div>

                    <div class="alert-custom alert-custom-danger" id="alertRateLimit" style="display:none;" role="alert">
                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0" style="margin-top:1px"></i>
                        <span id="alertRateLimitMsg"></span>
                    </div>

                    <form id="verifyForm" autocomplete="off" novalidate>
                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <label class="f-label" for="f_nik">NIK / Kode Pelaut</label>
                            <div class="form-group-custom">
                                <input type="text" class="form-control form-control-custom" id="f_nik" name="nik"
                                       placeholder="Masukkan NIK atau kode pelaut" maxlength="100">
                                <i class="bi bi-person-badge form-icon-prefix"></i>
                            </div>
                        </div>

                        <div class="or-divider">ATAU / OR</div>

                        <div class="mb-3">
                            <label class="f-label" for="f_cert">Nomor Sertifikat</label>
                            <div class="form-group-custom">
                                <input type="text" class="form-control form-control-custom" id="f_cert" name="nomor_sertifikat"
                                       placeholder="Masukkan nomor sertifikat" maxlength="100">
                                <i class="bi bi-patch-check form-icon-prefix"></i>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="f-label">Keamanan (Captcha)</label>
                            <div class="captcha-box-custom">
                                <span class="captcha-q-custom" id="captchaQuestion">— + — = ?</span>
                                <div class="captcha-info">
                                    <span class="captcha-info-label">Keamanan / Safety</span>
                                    <span class="captcha-info-desc text-muted">Isi jawaban operasi</span>
                                </div>
                            </div>
                            <div class="form-group-custom mb-1">
                                <input type="text" class="form-control form-control-custom" id="f_captcha" name="captcha_answer"
                                       placeholder="Hasil hitungan keamanan" inputmode="numeric" maxlength="4" autocomplete="off">
                                <i class="bi bi-shield-check form-icon-prefix"></i>
                            </div>
                            <input type="hidden" id="captchaExpected" name="captcha_expected">
                            <div class="invalid-feedback text-danger ms-1" id="errCaptcha" style="font-size:0.75rem; display:none;">
                                <i class="bi bi-x-circle me-1"></i>Jawaban captcha salah, silakan coba lagi.
                            </div>
                        </div>

                        <div class="alert-custom alert-custom-warning" id="alertInputErr" style="display:none;" role="alert">
                            <i class="bi bi-info-circle flex-shrink-0" style="margin-top:1px"></i>
                            <span id="alertInputErrMsg">Isi minimal satu field: NIK atau Nomor Sertifikat.</span>
                        </div>

                        <button type="submit" class="btn-verify-custom mt-3" id="btnVerify">
                            <span id="btnText" class="d-flex align-items-center gap-2">
                                <i class="bi bi-search"></i>
                                Cek Keaslian Sertifikat
                            </span>
                            <span id="btnSpinner" class="d-none">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                Memproses...
                            </span>
                        </button>
                    </form>

                    <p class="security-note">
                        <i class="bi bi-shield-lock me-1"></i>
                        Pencarian menampilkan semua data sertifikat yang terdaftar secara sah di dalam database sistem kami.
                    </p>
                </div>
            </div>
        </div>

        
        <div class="col-lg-7 col-xl-8">
            <div class="panel result-wrap" id="resultPanel">

                
                <div class="loading-overlay-custom d-none" id="loadingOverlay">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Sedang memverifikasi data sertifikat...</p>
                </div>

                
                <div class="empty-state-custom" id="stateDefault">
                    <div class="empty-state-icon-wrapper">
                        <i class="bi bi-clipboard2-check"></i>
                    </div>
                    <h5>Hasil Verifikasi Sertifikat</h5>
                    <p>Gunakan formulir pencarian di sebelah kiri untuk memverifikasi keaslian dan status sertifikat kru.</p>
                </div>

                
                <div class="empty-state-custom d-none" id="stateNotFound">
                    <div class="notfound-state-icon-wrapper">
                        <i class="bi bi-search"></i>
                    </div>
                    <h5 style="color:var(--rose-700)">Sertifikat Tidak Ditemukan</h5>
                    <p>NIK atau Nomor Sertifikat yang dimasukkan tidak cocok dengan data terdaftar di database kami. Periksa kembali input Anda.</p>
                </div>

                
                <div class="panel-body d-none" id="stateResults">
                    <div class="result-summary-bar">
                        <span class="result-summary-text" id="resultCount"></span>
                        <span class="badge bg-primary text-white px-3 py-2 rounded" style="font-size:0.7rem; font-weight:700;">TERVERIFIKASI</span>
                    </div>
                    <div id="resultList"></div>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
(function () {
    'use strict';

    const VERIFY_URL = '<?php echo e(route("certificate-verification.verify")); ?>';
    const CSRF       = document.querySelector('meta[name="csrf-token"]').content;

    // ── Init captcha from server ──
    document.getElementById('captchaQuestion').textContent = '<?php echo e($captcha["question"]); ?> = ?';
    document.getElementById('captchaExpected').value       = '<?php echo e($captcha["payload"]); ?>';

    function refreshCaptcha(data) {
        document.getElementById('captchaQuestion').textContent = data.question + ' = ?';
        document.getElementById('captchaExpected').value       = data.payload;
        document.getElementById('f_captcha').value = '';
    }

    // ── State management ──
    function showState(id) {
        ['stateDefault', 'stateNotFound', 'stateResults'].forEach(s => {
            document.getElementById(s).classList.toggle('d-none', s !== id);
        });
    }

    function setLoading(on) {
        document.getElementById('loadingOverlay').classList.toggle('d-none', !on);
        document.getElementById('btnVerify').disabled = on;
        document.getElementById('btnText').classList.toggle('d-none', on);
        document.getElementById('btnSpinner').classList.toggle('d-none', !on);
    }

    function hideAllErrors() {
        document.getElementById('alertRateLimit').style.display = 'none';
        document.getElementById('alertInputErr').style.display = 'none';
        document.getElementById('errCaptcha').style.display = 'none';
        document.getElementById('f_captcha').classList.remove('is-invalid');
        document.getElementById('f_nik').classList.remove('is-invalid');
        document.getElementById('f_cert').classList.remove('is-invalid');
    }

    // ── Badge and card styling helper ──
    function resultClass(result) {
        const r = (result || '').toLowerCase();
        if (r === 'lulus')       return 'lulus';
        if (r === 'pending')     return 'pending';
        if (r === 'tidak lulus') return 'tidak-lulus';
        return 'other';
    }

    function badgeIcon(result) {
        const r = (result || '').toLowerCase();
        if (r === 'lulus')       return 'bi-patch-check-fill';
        if (r === 'pending')     return 'bi-hourglass-split';
        if (r === 'tidak lulus') return 'bi-x-circle-fill';
        return 'bi-question-circle';
    }

    // ── HTML Escape helper ──
    function esc(s) {
        if (!s) return '—';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // ── Render Card function ──
    function renderCard(r) {
        const cls  = resultClass(r.result);
        const bIco = badgeIcon(r.result);

        const nameParts = (r.name || '').trim().split(' ');
        const initials = nameParts.length > 1 
            ? (nameParts[0].charAt(0) + nameParts[nameParts.length - 1].charAt(0)).toUpperCase()
            : (nameParts[0] ? nameParts[0].substring(0, 2).toUpperCase() : '??');

        const assessors = [r.assessor_mar, r.assessor_hse, r.assessor_fmc]
            .filter(Boolean).join(', ') || '—';

        const validPill = (r.valid_from && r.valid_until)
            ? `<div class="validity-block-custom">
                   <i class="bi bi-calendar3" style="font-size:.75rem"></i> 
                   Berlaku: ${esc(r.valid_from)} – ${esc(r.valid_until)}
               </div>`
            : '';

        const notesRow = r.notes
            ? `<div class="notes-block-custom">
                   <strong class="d-block text-slate-700 mb-1" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em;">Catatan / Notes:</strong>
                   <i class="bi bi-chat-text me-1 text-slate-400"></i>${esc(r.notes)}
               </div>`
            : '';

        return `
        <div class="cert-card-premium ${cls}">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="initials-avatar-badge">${initials}</div>
                    <div class="d-flex flex-column">
                        <span class="cert-name">${esc(r.name)}</span>
                        <span class="cert-position-badge">${esc(r.coc)}</span>
                    </div>
                </div>
                <span class="status-badge-custom ${cls}">
                    <i class="bi ${bIco}"></i> ${esc(r.result)}
                </span>
            </div>
            <div class="cert-meta-grid">
                <div class="meta-item-custom">
                    <span class="meta-label-custom"><i class="bi bi-patch-check"></i> No. Sertifikat</span>
                    <span class="meta-val-custom text-primary">${esc(r.certificate_number)}</span>
                </div>
                <div class="meta-item-custom">
                    <span class="meta-label-custom"><i class="bi bi-card-text"></i> NIK / Kode Pelaut</span>
                    <span class="meta-val-custom">${esc(r.nik)}</span>
                </div>
                <div class="meta-item-custom">
                    <span class="meta-label-custom"><i class="bi bi-person-workspace"></i> Jabatan Diusulkan</span>
                    <span class="meta-val-custom">${esc(r.position_proposed)}</span>
                </div>
                <div class="meta-item-custom">
                    <span class="meta-label-custom"><i class="bi bi-ship"></i> Kapal</span>
                    <span class="meta-val-custom">${esc(r.vessel)}</span>
                </div>
                <div class="meta-item-custom">
                    <span class="meta-label-custom"><i class="bi bi-building"></i> Perusahaan</span>
                    <span class="meta-val-custom">${esc(r.company)}</span>
                </div>
                <div class="meta-item-custom">
                    <span class="meta-label-custom"><i class="bi bi-calendar-check"></i> Tanggal Assessment</span>
                    <span class="meta-val-custom">${esc(r.assessment_date)}</span>
                </div>
                <div class="meta-item-custom col-span-2">
                    <span class="meta-label-custom"><i class="bi bi-people"></i> Assessor</span>
                    <span class="meta-val-custom">${esc(assessors)}</span>
                </div>
                ${r.mev_type ? `<div class="meta-item-custom"><span class="meta-label-custom"><i class="bi bi-sliders"></i> MEV</span><span class="meta-val-custom">${esc(r.mev_type)}</span></div>` : ''}
            </div>
            ${validPill}
            ${notesRow}
        </div>`;
    }

    // ── Form submit handler ──
    document.getElementById('verifyForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        hideAllErrors();

        const nik   = document.getElementById('f_nik').value.trim();
        const cert  = document.getElementById('f_cert').value.trim();
        const capt  = document.getElementById('f_captcha').value.trim();
        const exp   = document.getElementById('captchaExpected').value;

        if (!nik && !cert) {
            document.getElementById('alertInputErrMsg').textContent = 'Masukkan minimal satu parameter pencarian: NIK atau Nomor Sertifikat.';
            document.getElementById('alertInputErr').style.display = 'flex';
            document.getElementById('f_nik').classList.add('is-invalid');
            document.getElementById('f_cert').classList.add('is-invalid');
            return;
        }

        const body = new URLSearchParams({
            _token: CSRF,
            nik,
            nomor_sertifikat: cert,
            captcha_answer:   capt,
            captcha_expected: exp,
        });

        setLoading(true);

        try {
            const res  = await fetch(VERIFY_URL, {
                method:  'POST',
                headers: {
                    'Accept':       'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': CSRF,
                },
                body: body.toString(),
            });

            const data = await res.json();

            // Always refresh captcha on response
            if (data.captcha) refreshCaptcha(data.captcha);

            if (!res.ok) {
                if (data.error === 'rate_limit') {
                    document.getElementById('alertRateLimitMsg').textContent = data.message;
                    document.getElementById('alertRateLimit').style.display = 'flex';
                } else if (data.error === 'captcha') {
                    document.getElementById('errCaptcha').style.display = 'block';
                    document.getElementById('f_captcha').classList.add('is-invalid');
                } else if (data.error === 'input') {
                    document.getElementById('alertInputErrMsg').textContent = data.message;
                    document.getElementById('alertInputErr').style.display = 'flex';
                } else if (data.errors) {
                    const msgs = Object.values(data.errors).flat().join(' ');
                    document.getElementById('alertInputErrMsg').textContent = msgs;
                    document.getElementById('alertInputErr').style.display = 'flex';
                }
                setLoading(false);
                return;
            }

            setLoading(false);

            if (!data.results || data.results.length === 0) {
                showState('stateNotFound');
            } else {
                const n = data.results.length;
                document.getElementById('resultCount').innerHTML =
                    `Ditemukan <strong>${n}</strong> data sertifikat terdaftar`;
                document.getElementById('resultList').innerHTML =
                    data.results.map(renderCard).join('');
                showState('stateResults');
                
                // Smooth scroll to results on mobile
                if (window.innerWidth < 992) {
                    document.getElementById('resultPanel').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }

        } catch (err) {
            setLoading(false);
            console.error('Verification error:', err);
            document.getElementById('alertInputErrMsg').textContent =
                'Terjadi kesalahan jaringan. Periksa koneksi internet Anda dan coba lagi.';
            document.getElementById('alertInputErr').style.display = 'flex';
        }
    });

    // ── Enter key logic ──
    ['f_nik', 'f_cert', 'f_captcha'].forEach(id => {
        document.getElementById(id).addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('verifyForm').dispatchEvent(new Event('submit'));
            }
        });
    });

}());
</script>

</body>
</html>
<?php /**PATH /home/kaptensa/salman/resources/views/public/certificate-verification/index.blade.php ENDPATH**/ ?>