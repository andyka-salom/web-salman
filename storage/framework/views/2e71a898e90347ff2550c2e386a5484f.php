


<?php $__env->startSection('title', 'Health Check Dashboard'); ?>


<?php $__env->startPush('styles'); ?>
<link href="<?php echo e(asset('plugins/src/apex/apexcharts.css')); ?>" rel="stylesheet" type="text/css">
<link href="<?php echo e(asset('assets/css/light/components/list-group.css')); ?>" rel="stylesheet" type="text/css">
<link href="<?php echo e(asset('assets/css/light/dashboard/dash_2.css')); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo e(asset('assets/css/dark/components/list-group.css')); ?>" rel="stylesheet" type="text/css">
<link href="<?php echo e(asset('assets/css/dark/dashboard/dash_2.css')); ?>" rel="stylesheet" type="text/css" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    :root {
        --primary-color: #4361ee;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #e7515a;
        --dark-text: #1e293b;
        --muted-text: #64748b;
        --border-color: #e2e8f0;
    }

    /* Modern Card Style (Clean White) */
    .modern-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 4px rgba(148, 163, 184, 0.05);
        transition: all 0.3s ease;
    }

    /* Filter Card Specifics */
    .filter-card {
        background: #fff;
        border-radius: 12px;
        border-left: 5px solid var(--primary-color);
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .filter-card .form-label {
        color: var(--dark-text);
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 13px;
    }
    .filter-card .form-control,
    .filter-card .form-select {
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        border-radius: 8px;
        padding: 10px 15px;
        color: var(--dark-text);
        font-size: 14px;
        transition: all 0.2s;
    }
    .filter-card .form-control:focus,
    .filter-card .form-select:focus {
        background: #fff;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
    }

    /* Button Modern */
    .btn-filter {
        background-color: var(--primary-color);
        color: #fff;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        transition: transform 0.2s;
    }
    .btn-filter:hover {
        background-color: #304ffe;
        color: #fff;
        transform: translateY(-1px);
    }

    /* Select2 Customization */
    .select2-container--default .select2-selection--single {
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        border-radius: 8px;
        height: 48px;
        padding: 8px 15px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: var(--dark-text);
        line-height: 30px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px;
    }

    /* Badges Modern */
    .badge-modern {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.3px;
    }
    .bg-soft-success { background: #d1fae5; color: #065f46; }
    .bg-soft-warning { background: #fef3c7; color: #92400e; }
    .bg-soft-danger { background: #fee2e2; color: #991b1b; }
    .bg-soft-primary { background: #e0e7ff; color: #3730a3; }

    /* Info Tooltip for Chart Legends */
    .chart-info {
        font-size: 11px;
        color: var(--muted-text);
        margin-top: 8px;
        padding: 8px 12px;
        background: #f8fafc;
        border-radius: 6px;
        border-left: 3px solid var(--primary-color);
    }

    /* Custom Widget Overrides */
    .widget-t-sales-widget { border: 1px solid var(--border-color); box-shadow: none; }
    .widget-heading h5 { color: var(--dark-text); font-weight: 700; }
    .widget-numeric-value { color: var(--dark-text) !important; font-weight: 800 !important; }
    .widget-text { color: var(--muted-text) !important; }

    /* Table Styling */
    .table > thead > tr > th {
        background: #f8fafc;
        color: var(--muted-text);
        font-weight: 700;
        border-bottom: 1px solid var(--border-color);
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
        padding: 16px 12px;
        vertical-align: middle;
    }
    .table td {
        vertical-align: middle;
        border-bottom: 1px solid var(--border-color);
        padding: 14px 12px;
    }
    .table tbody tr:hover {
        background-color: #f8fafc;
        transition: background-color 0.2s ease;
        cursor: pointer;
    }

    /* Progress bar improvements */
    .progress {
        border-radius: 6px;
        overflow: hidden;
    }
    .progress-bar {
        transition: width 0.6s ease;
    }

    /* Modal Styling */
    .modal-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, #304ffe 100%);
        color: white;
        border-radius: 12px 12px 0 0;
    }
    .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    }
    .modal-body {
        max-height: 65vh;
        overflow-y: auto;
    }

    /* Timeline Style for Daily Details */
    .timeline-item {
        border-left: 3px solid var(--border-color);
        padding-left: 20px;
        padding-bottom: 20px;
        position: relative;
    }
    .timeline-item:last-child {
        padding-bottom: 0;
        border-left: 3px solid transparent;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -7px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid var(--primary-color);
    }
    .timeline-item.excellent::before { border-color: var(--success-color); }
    .timeline-item.good::before { border-color: #2196f3; }
    .timeline-item.warning::before { border-color: var(--warning-color); }
    .timeline-item.critical::before { border-color: var(--danger-color); }

    .timeline-item.excellent { border-left-color: var(--success-color); }
    .timeline-item.good { border-left-color: #2196f3; }
    .timeline-item.warning { border-left-color: var(--warning-color); }
    .timeline-item.critical { border-left-color: var(--danger-color); }

    /* Dark Mode Adjustments */
    body.dark .modern-card, body.dark .filter-card { background: #191e3a; border-color: #3b3f5c; }
    body.dark .filter-card .form-label { color: #bfc9d4; }
    body.dark .filter-card .form-control { background: #0e1726; border-color: #3b3f5c; color: #bfc9d4; }
    body.dark .chart-info { background: #0e1726; border-left-color: var(--primary-color); color: #888ea8; }
    body.dark .modal-content { background: #191e3a; }
    body.dark .timeline-item { border-left-color: #3b3f5c; }
    body.dark .timeline-item::before { background: #191e3a; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="row layout-top-spacing">

    
    <div class="col-12 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark mb-1">Health Dashboard</h3>
                <p class="text-muted mb-0" style="font-size: 14px;">
                    Memantau kesehatan kru dan kepatuhan kapal.
                    Periode:
                    <span class="text-dark fw-bold">
                        <?php echo e(\Carbon\Carbon::parse(request('start_date', now()))->format('d M Y')); ?>

                        -
                        <?php echo e(\Carbon\Carbon::parse(request('end_date', now()))->format('d M Y')); ?>

                    </span>
                </p>
            </div>
            <div>
                <span class="badge badge-modern bg-soft-primary border border-primary-subtle">
                    <?php if (isset($component)) { $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feather-icon','data' => ['name' => 'activity','class' => 'me-1','style' => 'width: 14px; height: 14px;']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feather-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'activity','class' => 'me-1','style' => 'width: 14px; height: 14px;']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $attributes = $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $component = $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
                    Live Monitoring
                </span>
            </div>
        </div>
    </div>

    
    <div class="col-12 layout-spacing">
        <div class="filter-card">
            <form method="GET" action="<?php echo e(route('dashboard.health-check')); ?>" id="filterForm">
                <div class="row align-items-end">

                    
                    <?php if(auth()->user()->hasAnyRole(['nurse', 'super-admin'])): ?>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label for="company_id" class="form-label">Perusahaan</label>
                        <select name="company_id" id="company_id" class="form-select select2-company">
                            <option value="">Semua Perusahaan</option>
                            <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($company->id); ?>" <?php echo e(request('company_id') == $company->id ? 'selected' : ''); ?>>
                                    <?php echo e($company->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <?php else: ?>
                    <div class="col-md-4 mb-3 mb-md-0 d-none d-md-block">
                        <div class="d-flex align-items-center text-muted">
                            <?php if (isset($component)) { $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feather-icon','data' => ['name' => 'filter','class' => 'me-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feather-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'filter','class' => 'me-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $attributes = $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $component = $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
                            <span>Filter Data Statistik</span>
                        </div>
                    </div>
                    <?php endif; ?>

                    
                    <div class="col-md-3 mb-3 mb-md-0">
                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="start_date" class="form-control"
                               value="<?php echo e(request('start_date', now()->format('Y-m-d'))); ?>">
                    </div>

                    <div class="col-md-3 mb-3 mb-md-0">
                        <label for="end_date" class="form-label">Tanggal Akhir</label>
                        <input type="date" name="end_date" id="end_date" class="form-control"
                               value="<?php echo e(request('end_date', now()->format('Y-m-d'))); ?>">
                    </div>

                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-filter w-100 h-100 py-2 shadow-sm">
                            Terapkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 layout-spacing">
        <div class="widget widget-t-sales-widget widget-m-sales h-100">
            <div class="media">
                <div class="icon ml-2 text-primary bg-soft-primary p-2 rounded">
                    <?php if (isset($component)) { $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feather-icon','data' => ['name' => 'anchor']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feather-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'anchor']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $attributes = $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $component = $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
                </div>
                <div class="media-body">
                    <p class="widget-text mb-1">Total Kapal</p>
                    <p class="widget-numeric-value fs-4 mb-0"><?php echo e($totalVessels); ?></p>
                </div>
            </div>
            <p class="widget-total-stats mt-2 text-muted" style="font-size: 12px;"><?php echo e($activeVessels); ?> Kapal Beroperasi</p>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 layout-spacing">
        <div class="widget widget-t-sales-widget widget-m-orders h-100">
            <div class="media">
                <div class="icon ml-2 text-info bg-soft-primary p-2 rounded" style="color: #4361ee; background: #e0e7ff;">
                    <?php if (isset($component)) { $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feather-icon','data' => ['name' => 'users']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feather-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'users']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $attributes = $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $component = $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
                </div>
                <div class="media-body">
                    <p class="widget-text mb-1">Total Kru</p>
                    <p class="widget-numeric-value fs-4 mb-0"><?php echo e($totalCrew); ?></p>
                </div>
            </div>
            <p class="widget-total-stats mt-2 text-muted" style="font-size: 12px;"><?php echo e($activeCrew); ?> Kru Aktif</p>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 layout-spacing">
        <div class="widget widget-t-sales-widget widget-m-customers h-100">
            <div class="media">
                <div class="icon ml-2 text-success bg-soft-success p-2 rounded">
                    <?php if (isset($component)) { $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feather-icon','data' => ['name' => 'check-circle']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feather-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check-circle']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $attributes = $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $component = $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
                </div>
                <div class="media-body">
                    <p class="widget-text mb-1">Kepatuhan DCU</p>
                    <p class="widget-numeric-value fs-4 mb-0"><?php echo e(number_format($complianceRate, 1)); ?>%</p>
                </div>
            </div>
            <p class="widget-total-stats mt-2 text-muted" style="font-size: 12px;"><?php echo e($totalChecks); ?> Laporan Masuk</p>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 layout-spacing">
        <div class="widget widget-t-sales-widget widget-m-customers h-100">
            <div class="media">
                <div class="icon ml-2 text-success bg-soft-success p-2 rounded">
                    <?php if (isset($component)) { $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feather-icon','data' => ['name' => 'clipboard']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feather-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clipboard']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $attributes = $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $component = $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
                </div>
                <div class="media-body">
                    <p class="widget-text mb-1">Kepatuhan MCU</p>
                    <p class="widget-numeric-value fs-4 mb-0"><?php echo e(number_format($mcuComplianceRate, 1)); ?>%</p>
                </div>
            </div>
            <p class="widget-total-stats mt-2 text-muted" style="font-size: 12px;"><?php echo e($validMcuCount); ?> / <?php echo e($activeCrew); ?> Kru Valid</p>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 layout-spacing">
        <div class="widget widget-t-sales-widget widget-m-income h-100">
            <div class="media">
                <div class="icon ml-2 text-danger bg-soft-danger p-2 rounded">
                    <?php if (isset($component)) { $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feather-icon','data' => ['name' => 'activity']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feather-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'activity']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $attributes = $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $component = $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
                </div>
                <div class="media-body">
                    <p class="widget-text mb-1">Isu Kesehatan</p>
                    <p class="widget-numeric-value fs-4 mb-0 text-danger"><?php echo e($healthIssues); ?></p>
                </div>
            </div>
            <p class="widget-total-stats mt-2 text-muted" style="font-size: 12px;"><?php echo e($requiresFollowUp); ?> Perlu Tindak Lanjut</p>
        </div>
    </div>

    <div class="col-12 d-none d-xl-block"></div>

    
    <div class="col-xl-8 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
        <div class="widget widget-chart-one h-100">
            <div class="widget-heading d-flex justify-content-between align-items-center mb-3">
                <h5 class="">Tren Kesehatan Harian</h5>
                <span class="badge bg-light text-dark border">
                    <?php echo e(\Carbon\Carbon::parse(request('start_date', now()))->format('d M')); ?> -
                    <?php echo e(\Carbon\Carbon::parse(request('end_date', now()))->format('d M')); ?>

                </span>
            </div>
            <div class="widget-content">
                <div id="complianceTrendChart"></div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
        <div class="widget widget-three h-100">
            <div class="widget-heading mb-4">
                <h5 class="">Ringkasan jumlah pemeriksaan</h5>
            </div>
            <div class="widget-content">
                <div class="order-summary">

                    <!-- Healthy -->
                    <div class="summary-list mb-4">
                        <div class="w-icon bg-soft-success text-success border border-success border-opacity-25">
                            <?php if (isset($component)) { $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feather-icon','data' => ['name' => 'check']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feather-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $attributes = $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $component = $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
                        </div>
                        <div class="w-summary-details">
                            <div class="w-summary-info">
                                <h6 class="mb-0 fw-bold">Sehat / Normal</h6>
                                <p class="summary-count fw-bold text-success"><?php echo e($normalHealthCount); ?></p>
                            </div>
                            <div class="w-summary-stats mt-2">
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                         style="width: <?php echo e($normalHealthPercentage); ?>%"></div>
                                </div>
                                <small class="text-muted mt-1 d-block"><?php echo e(number_format($normalHealthPercentage, 1)); ?>% dari total</small>
                            </div>
                        </div>
                    </div>

                    <!-- Warning -->
                    <div class="summary-list mb-4">
                        <div class="w-icon bg-soft-warning text-warning border border-warning border-opacity-25">
                            <?php if (isset($component)) { $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feather-icon','data' => ['name' => 'alert-circle']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feather-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'alert-circle']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $attributes = $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $component = $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
                        </div>
                        <div class="w-summary-details">
                            <div class="w-summary-info">
                                <h6 class="mb-0 fw-bold">Perlu Perhatian</h6>
                                <p class="summary-count fw-bold text-warning"><?php echo e($attentionHealthCount); ?></p>
                            </div>
                            <div class="w-summary-stats mt-2">
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-warning" role="progressbar"
                                         style="width: <?php echo e($attentionHealthPercentage); ?>%"></div>
                                </div>
                                <small class="text-muted mt-1 d-block"><?php echo e(number_format($attentionHealthPercentage, 1)); ?>% dari total</small>
                            </div>
                        </div>
                    </div>

                    <!-- Critical -->
                    <div class="summary-list">
                        <div class="w-icon bg-soft-danger text-danger border border-danger border-opacity-25">
                            <?php if (isset($component)) { $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feather-icon','data' => ['name' => 'activity']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feather-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'activity']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $attributes = $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $component = $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
                        </div>
                        <div class="w-summary-details">
                            <div class="w-summary-info">
                                <h6 class="mb-0 fw-bold">Kritis / Sakit</h6>
                                <p class="summary-count fw-bold text-danger"><?php echo e($criticalHealthCount); ?></p>
                            </div>
                            <div class="w-summary-stats mt-2">
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-danger" role="progressbar"
                                         style="width: <?php echo e($criticalHealthPercentage); ?>%"></div>
                                </div>
                                <small class="text-muted mt-1 d-block"><?php echo e(number_format($criticalHealthPercentage, 1)); ?>% dari total</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    
    <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12 layout-spacing">
        <div class="widget widget-chart-two h-100">
            <div class="widget-heading">
                <h5 class="">Tekanan Darah</h5>
            </div>
            <div class="widget-content">
                <div id="bloodPressureChart"></div>
                <div class="chart-info">
                    <strong>Hipotensi:</strong> &lt;90 systolic atau &lt;60 diastolic |
                    <strong>Normal:</strong> 90-120/60-80 |
                    <strong>Pre Hipertensi:</strong> &gt;120-140 systolic atau &gt;80 diastolic |
                    <strong>HT 1:</strong> &gt;140-159 systolic |
                    <strong>HT 2:</strong> &gt;159 systolic
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12 layout-spacing">
        <div class="widget widget-chart-two h-100">
            <div class="widget-heading">
                <h5 class="">Denyut Nadi</h5>
            </div>
            <div class="widget-content">
                <div id="pulseRateChart"></div>
                <div class="chart-info">
                    <strong>Bradikardi:</strong> &lt;60 bpm  |
                    <strong>Normal:</strong> 60-100 bpm  |
                    <strong>Takikardi:</strong> &gt;100 bpm
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12 layout-spacing">
        <div class="widget widget-chart-two h-100">
            <div class="widget-heading">
                <h5 class="">Suhu Tubuh</h5>
            </div>
            <div class="widget-content">
                <div id="temperatureChart"></div>
                <div class="chart-info">
                    <strong>Hipotermi:</strong> &lt;36°C  |
                    <strong>Normal:</strong> 36-37.5°C  |
                    <strong>Hipertermi:</strong> &gt;37.5°C
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12 layout-spacing">
        <div class="widget widget-chart-two h-100">
            <div class="widget-heading">
                <h5 class="">Laju Pernapasan</h5>
            </div>
            <div class="widget-content">
                <div id="respiratoryRateChart"></div>
                <div class="chart-info">
                    <strong>Bradipnea:</strong> &lt;12 x/menit  |
                    <strong>Normal:</strong> 12-20 x/menit  |
                    <strong>Takipnea:</strong> &gt;20 x/menit
                </div>
            </div>
        </div>
    </div>

    
    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
        <div class="widget widget-table-two">
            <div class="widget-heading">
                <h5 class="">Kepatuhan Per Kapal</h5>
            </div>
            <div class="widget-content">
                <div class="table-responsive">
                    <table class="table table-hover" id="vesselComplianceTable">
                        <thead>
                            <tr>
                                <th style="width: 25%;">Nama Kapal</th>
                                <th class="text-center" style="width: 12%;">Kru Aktif (actual)</th>
                                <th class="text-center" style="width: 15%;">Cek / Target</th>
                                <th style="width: 35%;">Progress Kepatuhan</th>
                                <th class="text-center" style="width: 13%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $vesselCompliance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vessel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="vessel-row" data-vessel-id="<?php echo e($vessel['id']); ?>" data-vessel-name="<?php echo e($vessel['name']); ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2 bg-light-primary text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px;">
                                            <?php if (isset($component)) { $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feather-icon','data' => ['name' => 'anchor','style' => 'width: 16px; height: 16px;']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feather-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'anchor','style' => 'width: 16px; height: 16px;']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $attributes = $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $component = $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
                                        </div>
                                        <span class="fw-bold text-dark"><?php echo e($vessel['name']); ?></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border"><?php echo e(number_format($vessel['total_crew'])); ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-dark"><?php echo e(number_format($vessel['checks_count'])); ?></span>
                                    <span class="text-muted"> / <?php echo e(number_format($vessel['target_checks'])); ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-3" style="height: 8px; background-color: #e2e8f0;">
                                            <?php
                                                $barClass = 'bg-danger';
                                                if($vessel['status'] === 'excellent') $barClass = 'bg-success';
                                                elseif($vessel['status'] === 'good') $barClass = 'bg-info';
                                                elseif($vessel['status'] === 'warning') $barClass = 'bg-warning';

                                                $displayRate = number_format($vessel['compliance_rate'], 1);
                                            ?>
                                            <div class="progress-bar <?php echo e($barClass); ?>" role="progressbar"
                                                 style="width: <?php echo e(min($vessel['compliance_rate'], 100)); ?>%; border-radius: 4px;"
                                                 aria-valuenow="<?php echo e($vessel['compliance_rate']); ?>"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                        <span class="fw-bold text-dark" style="font-size: 14px; min-width: 50px; text-align: right;">
                                            <?php echo e($displayRate); ?>%
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if($vessel['status'] === 'excellent'): ?>
                                        <span class="badge badge-modern bg-soft-success">Sangat Baik</span>
                                    <?php elseif($vessel['status'] === 'good'): ?>
                                        <span class="badge badge-modern bg-soft-primary">Baik</span>
                                    <?php elseif($vessel['status'] === 'warning'): ?>
                                        <span class="badge badge-modern bg-soft-warning">Cukup</span>
                                    <?php else: ?>
                                        <span class="badge badge-modern bg-soft-danger">Buruk</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <?php if (isset($component)) { $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feather-icon','data' => ['name' => 'inbox','style' => 'width: 48px; height: 48px;','class' => 'text-muted mb-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feather-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'inbox','style' => 'width: 48px; height: 48px;','class' => 'text-muted mb-3']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $attributes = $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $component = $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
                                        <p class="text-muted mb-0 fw-semibold">Tidak ada data kapal.</p>
                                        <small class="text-muted">Silakan pilih filter atau tambahkan data kapal terlebih dahulu.</small>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="vesselDetailModal" tabindex="-1" aria-labelledby="vesselDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="vesselDetailModalLabel">
                        <?php if (isset($component)) { $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feather-icon','data' => ['name' => 'anchor','class' => 'me-2','style' => 'width: 20px; height: 20px;']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feather-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'anchor','class' => 'me-2','style' => 'width: 20px; height: 20px;']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $attributes = $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $component = $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
                        <span id="modalVesselName">Detail Kapal</span>
                    </h5>
                    <small class="text-white-50">Riwayat kepatuhan harian per tanggal</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="vesselDetailContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Memuat data...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('plugins/src/apex/apexcharts.min.js')); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.select2-company').select2({
            placeholder: 'Pilih Perusahaan',
            allowClear: true,
            width: '100%',
            dropdownParent: $('.filter-card')
        });

        // Handle Vessel Row Click
        $('.vessel-row').on('click', function() {
            const vesselId = $(this).data('vessel-id');
            const vesselName = $(this).data('vessel-name');
            const startDate = $('#start_date').val() || '<?php echo e(request("start_date", now()->format("Y-m-d"))); ?>';
            const endDate = $('#end_date').val() || '<?php echo e(request("end_date", now()->format("Y-m-d"))); ?>';

            // Update modal title
            $('#modalVesselName').text(vesselName);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('vesselDetailModal'));
            modal.show();

            // Reset content to loading state
            $('#vesselDetailContent').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Memuat data...</p>
                </div>
            `);

            // Fetch vessel detail
            $.ajax({
                url: '<?php echo e(route("dashboard.health-check.vessel-detail")); ?>',
                method: 'GET',
                data: {
                    vessel_id: vesselId,
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {
                    renderVesselDetail(response);
                },
                error: function(xhr) {
                    $('#vesselDetailContent').html(`
                        <div class="alert alert-danger text-center">
                            <?php if (isset($component)) { $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feather-icon','data' => ['name' => 'alert-circle','class' => 'mb-2','style' => 'width: 32px; height: 32px;']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feather-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'alert-circle','class' => 'mb-2','style' => 'width: 32px; height: 32px;']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $attributes = $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $component = $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
                            <p class="mb-0">Gagal memuat data. Silakan coba lagi.</p>
                        </div>
                    `);
                }
            });
        });

        function renderVesselDetail(data) {
            let html = '<div class="timeline">';

            if (data.daily_details.length === 0) {
                html = `
                    <div class="text-center py-5">
                        <?php if (isset($component)) { $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feather-icon','data' => ['name' => 'inbox','style' => 'width: 48px; height: 48px;','class' => 'text-muted mb-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feather-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'inbox','style' => 'width: 48px; height: 48px;','class' => 'text-muted mb-3']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $attributes = $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $component = $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
                        <p class="text-muted mb-0">Tidak ada data untuk periode ini.</p>
                    </div>
                `;
            } else {
                data.daily_details.forEach(function(day, index) {
                    const statusClass = day.status;
                    let statusBadge = '';
                    let statusText = '';

                    switch(day.status) {
                        case 'excellent':
                            statusBadge = 'bg-soft-success';
                            statusText = 'Sangat Baik';
                            break;
                        case 'good':
                            statusBadge = 'bg-soft-primary';
                            statusText = 'Baik';
                            break;
                        case 'warning':
                            statusBadge = 'bg-soft-warning';
                            statusText = 'Cukup';
                            break;
                        default:
                            statusBadge = 'bg-soft-danger';
                            statusText = 'Buruk';
                    }

                    html += `
                        <div class="timeline-item ${statusClass}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="fw-bold mb-1">${day.date}</h6>
                                    <span class="badge badge-modern ${statusBadge}">${statusText}</span>
                                </div>
                                <div class="text-end">
                                    <h4 class="mb-0 fw-bold text-primary">${day.compliance_rate}%</h4>
                                    <small class="text-muted">Kepatuhan</small>
                                </div>
                            </div>
                            <div class="row g-3 mt-2">
                                <div class="col-4">
                                    <div class="p-3 bg-light rounded">
                                        <div class="d-flex align-items-center mb-1">
                                            <?php if (isset($component)) { $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feather-icon','data' => ['name' => 'users','style' => 'width: 16px; height: 16px;','class' => 'text-muted me-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feather-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'users','style' => 'width: 16px; height: 16px;','class' => 'text-muted me-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $attributes = $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $component = $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
                                            <small class="text-muted">Kru Aktif</small>
                                        </div>
                                        <h5 class="mb-0 fw-bold">${day.active_crew}</h5>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-3 bg-light rounded">
                                        <div class="d-flex align-items-center mb-1">
                                            <?php if (isset($component)) { $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feather-icon','data' => ['name' => 'check-circle','style' => 'width: 16px; height: 16px;','class' => 'text-success me-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feather-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check-circle','style' => 'width: 16px; height: 16px;','class' => 'text-success me-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $attributes = $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $component = $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
                                            <small class="text-muted">DCU Masuk</small>
                                        </div>
                                        <h5 class="mb-0 fw-bold text-success">${day.checks_done}</h5>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-3 bg-light rounded">
                                        <div class="d-flex align-items-center mb-1">
                                            <?php if (isset($component)) { $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feather-icon','data' => ['name' => 'x-circle','style' => 'width: 16px; height: 16px;','class' => 'text-danger me-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feather-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-circle','style' => 'width: 16px; height: 16px;','class' => 'text-danger me-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $attributes = $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $component = $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
                                            <small class="text-muted">Belum DCU</small>
                                        </div>
                                        <h5 class="mb-0 fw-bold text-danger">${day.active_crew - day.checks_done}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 8px;">
                                <div class="progress-bar ${statusClass === 'excellent' ? 'bg-success' : statusClass === 'good' ? 'bg-info' : statusClass === 'warning' ? 'bg-warning' : 'bg-danger'}"
                                     role="progressbar"
                                     style="width: ${Math.min(day.compliance_rate, 100)}%"
                                     aria-valuenow="${day.compliance_rate}"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    `;
                });
            }

            html += '</div>';
            $('#vesselDetailContent').html(html);
        }
    });

    // --- Modern Color Palette ---
    const colors = {
        primary: '#4361ee',
        success: '#10b981',
        warning: '#f59e0b',
        danger:  '#e7515a',
        info:    '#2196f3',
        gray:    '#888ea8'
    };

    // 1. TREND CHART (Area Chart)
    var trendOptions = {
        chart: {
            height: 350,
            type: 'area',
            fontFamily: 'Nunito, sans-serif',
            toolbar: { show: false },
            zoom: { enabled: false }
        },
        colors: [colors.success, colors.danger],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        series: [{
            name: 'Kondisi Normal',
            data: <?php echo json_encode($complianceTrendData['healthy'], 15, 512) ?>
        }, {
            name: 'Ada Gejala/Sakit',
            data: <?php echo json_encode($complianceTrendData['warning'], 15, 512) ?>
        }],
        xaxis: {
            categories: <?php echo json_encode($complianceTrendData['labels'], 15, 512) ?>,
            axisBorder: { show: false },
            axisTicks: { show: false },
            tooltip: { enabled: false },
            labels: { style: { colors: colors.gray, fontSize: '12px' } }
        },
        yaxis: {
            labels: {
                formatter: function (val) { return val.toFixed(0); },
                style: { colors: colors.gray }
            }
        },
        fill: {
            type: "gradient",
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.05,
                stops: [0, 100]
            }
        },
        grid: {
            borderColor: '#e2e8f0',
            strokeDashArray: 4,
            yaxis: { lines: { show: true } }
        },
        legend: { position: 'top', horizontalAlign: 'right' },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + " crew"
                }
            }
        }
    };
    new ApexCharts(document.querySelector("#complianceTrendChart"), trendOptions).render();

    // 2. DONUT CHARTS (Reusable Modern Config)
    var donutConfig = {
        chart: {
            type: 'donut',
            height: 280,
            fontFamily: 'Nunito, sans-serif'
        },
        dataLabels: { enabled: false },
        plotOptions: {
            pie: {
                donut: {
                    size: '70%',
                    labels: {
                        show: true,
                        name: {
                            show: true,
                            fontSize: '16px',
                            color: colors.gray,
                            offsetY: -5
                        },
                        value: {
                            show: true,
                            fontSize: '22px',
                            fontWeight: 700,
                            color: '#1e293b',
                            offsetY: 5,
                            formatter: function (val) { return val }
                        },
                        total: {
                            show: true,
                            showAlways: true,
                            label: 'Total',
                            fontSize: '14px',
                            color: colors.gray,
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                            }
                        }
                    }
                }
            }
        },
        stroke: { show: true, width: 2, colors: ['#fff'] },
        legend: {
            position: 'bottom',
            horizontalAlign: 'center',
            fontSize: '13px',
            markers: {
                width: 10,
                height: 10,
                radius: 2
            }
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    height: 250
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    // Blood Pressure (Hipotensi: <90 systolic atau <60 diastolic | Normal: 90-120/60-80 | Pre Hipertensi: >120-140 atau >80 | HT 1: >140-159 | HT 2: >159)
    var bpOpts = Object.assign({}, donutConfig);
    bpOpts.series = <?php echo json_encode(array_values($bloodPressureDistribution), 15, 512) ?>;
    bpOpts.labels = <?php echo json_encode(array_keys($bloodPressureDistribution), 15, 512) ?>;
    bpOpts.colors = [colors.danger, colors.success, colors.warning, colors.info, '#805dca'];
    new ApexCharts(document.querySelector("#bloodPressureChart"), bpOpts).render();

    // Pulse Rate (Bradikardi: <60 | Normal: 60-100 bpm | Takikardi: >100)
    var pulseOpts = Object.assign({}, donutConfig);
    pulseOpts.series = <?php echo json_encode(array_values($pulseRateDistribution), 15, 512) ?>;
    pulseOpts.labels = <?php echo json_encode(array_keys($pulseRateDistribution), 15, 512) ?>;
    pulseOpts.colors = [colors.info, colors.success, colors.danger];
    new ApexCharts(document.querySelector("#pulseRateChart"), pulseOpts).render();

    // Temperature (Hipotermi: <36°C | Normal: 36-37.5°C | Hipertermi: >37.5°C)
    var tempOpts = Object.assign({}, donutConfig);
    tempOpts.series = <?php echo json_encode(array_values($temperatureDistribution), 15, 512) ?>;
    tempOpts.labels = <?php echo json_encode(array_keys($temperatureDistribution), 15, 512) ?>;
    tempOpts.colors = [colors.info, colors.success, colors.danger];
    new ApexCharts(document.querySelector("#temperatureChart"), tempOpts).render();

    // Respiratory Rate (Bradipnea: <12 | Normal: 12-20 x/menit | Takipnea: >20)
    var respOpts = Object.assign({}, donutConfig);
    respOpts.series = <?php echo json_encode(array_values($respiratoryRateDistribution), 15, 512) ?>;
    respOpts.labels = <?php echo json_encode(array_keys($respiratoryRateDistribution), 15, 512) ?>;
    respOpts.colors = [colors.info, colors.success, colors.danger];
    new ApexCharts(document.querySelector("#respiratoryRateChart"), respOpts).render();

</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/dashboard/health-check.blade.php ENDPATH**/ ?>