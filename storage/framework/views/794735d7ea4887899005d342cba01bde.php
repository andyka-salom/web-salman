

<?php $__env->startSection('title', 'Dashboard - CERMAT Analytics'); ?>


<?php $__env->startPush('styles'); ?>
<link href="<?php echo e(asset('plugins/src/apex/apexcharts.css')); ?>" rel="stylesheet" type="text/css">
<link href="<?php echo e(asset('assets/css/light/dashboard/dash_2.css')); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo e(asset('assets/css/dark/dashboard/dash_2.css')); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo e(asset('assets/css/light/components/list-group.css')); ?>" rel="stylesheet" type="text/css">
<link href="<?php echo e(asset('assets/css/dark/components/list-group.css')); ?>" rel="stylesheet" type="text/css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* CUSTOM DASHBOARD STYLES */
    .widget {
        height: 100%;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
    }
    .widget-heading {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .widget-heading h5 {
        font-size: 15px;
        font-weight: 700;
        margin: 0;
        color: #1e293b;
    }
    .widget-content {
        padding: 20px;
        flex-grow: 1;
        position: relative;
    }

    /* KPI Cards */
    .kpi-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 24px;
        height: 100%;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px -5px rgba(0,0,0,0.1);
        border-color: #cbd5e1;
    }
    .kpi-icon-wrapper {
        width: 46px;
        height: 46px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 16px;
    }
    .kpi-value {
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 4px;
        line-height: 1.2;
    }
    .kpi-label {
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .kpi-subtext {
        font-size: 12px;
        font-weight: 500;
        margin-top: 8px;
    }

    /* Filters */
    .filter-container {
        background: #fff;
        padding: 15px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .filter-label {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 5px;
        display: block;
    }
    .form-control-sm, .form-select-sm {
        border-radius: 6px;
        border-color: #cbd5e1;
        font-size: 13px;
    }

    /* Select2 Fixes */
    .select2-container .select2-selection--single {
        height: 33px !important;
        border-color: #cbd5e1 !important;
        border-radius: 6px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 31px !important;
        font-size: 13px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 31px !important;
    }

    /* Dark Mode */
    body.dark .widget, body.dark .kpi-card, body.dark .filter-container {
        background: #1e293b;
        border-color: #334155;
    }
    body.dark .widget-heading { border-bottom-color: #334155; }
    body.dark .widget-heading h5, body.dark .kpi-value { color: #f1f5f9; }
    body.dark .kpi-label, body.dark .filter-label { color: #94a3b8; }
    body.dark .form-control-sm, body.dark .form-select-sm {
        background-color: #0f172a; border-color: #334155; color: #f1f5f9;
    }
    body.dark .select2-container .select2-selection--single {
        background-color: #0f172a !important; border-color: #334155 !important;
    }
    body.dark .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #f1f5f9 !important;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="layout-px-spacing">

    
    <div class="row layout-top-spacing mb-4">
        <div class="col-12">
            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">

                
                <div>
                    <h3 class="fw-bold mb-1">Dashboard Cermat</h3>
                    <p class="text-muted mb-0">
                        Periode Data:
                        <span class="text-primary fw-bold" id="period-display">Loading...</span>
                    </p>
                </div>

                
                <div class="filter-container d-flex flex-wrap gap-3 align-items-end">

                    
                    <?php if(auth()->user()->hasRole('super-admin')): ?>
                    <div class="filter-group">
                        <label class="filter-label">Perusahaan</label>
                        <select id="company_id" class="form-select form-select-sm select2-company" style="width: 220px;">
                            
                            <option value="">Semua Perusahaan</option>
                            <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($company->id); ?>"><?php echo e($company->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="filter-group">
                        <label class="filter-label">Tanggal Mulai</label>
                        <input type="date" id="start_date" class="form-control form-control-sm"
                               value="<?php echo e(\Carbon\Carbon::now()->startOfYear()->format('Y-m-d')); ?>">
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Tanggal Akhir</label>
                        <input type="date" id="end_date" class="form-control form-control-sm"
                               value="<?php echo e(\Carbon\Carbon::now()->format('Y-m-d')); ?>">
                    </div>

                    <div class="filter-group">
                        <button class="btn btn-primary btn-sm px-3" onclick="refreshDashboard()" style="height: 33px;">
                            <i class="fas fa-filter me-1"></i> Terapkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row mb-4 g-3">
        <!-- Total Reports -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="kpi-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="kpi-value" id="total-reports">-</div>
                        <div class="kpi-label">Total Laporan</div>
                    </div>
                    <div class="kpi-icon-wrapper bg-light-primary text-primary">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
                <div class="kpi-subtext text-muted">Status: Submitted</div>
            </div>
        </div>

        <!-- Closure Rate -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="kpi-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="kpi-value text-success" id="closure-rate">-</div>
                        <div class="kpi-label">Closure Rate</div>
                    </div>
                    <div class="kpi-icon-wrapper bg-light-success text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="kpi-subtext text-success">Laporan Selesai</div>
            </div>
        </div>

        <!-- Overdue Actions -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="kpi-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="kpi-value text-danger" id="overdue-actions">-</div>
                        <div class="kpi-label">Action Overdue</div>
                    </div>
                    <div class="kpi-icon-wrapper bg-light-danger text-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="kpi-subtext text-danger">Butuh Tindakan</div>
            </div>
        </div>

        <!-- Action Progress -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="kpi-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="kpi-value text-info" id="close-out-progress">-</div>
                        <div class="kpi-label">Action Progress</div>
                    </div>
                    <div class="kpi-icon-wrapper bg-light-info text-info">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                </div>
                <div class="kpi-subtext text-info">Tingkat Penyelesaian</div>
            </div>
        </div>
    </div>

    
    <div class="row mb-4 g-3">
        <div class="col-xl-8 col-lg-12">
            <div class="widget">
                <div class="widget-heading">
                    <h5>Tren Laporan Bulanan</h5>
                </div>
                <div class="widget-content">
                    <div id="trend-chart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-12">
            <div class="widget">
                <div class="widget-heading">
                    <h5>Klasifikasi Temuan</h5>
                </div>
                <div class="widget-content">
                    <div id="classification-chart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row mb-4 g-3">
        <div class="col-xl-6 col-lg-12">
            <div class="widget">
                <div class="widget-heading">
                    <h5>Status Action Item</h5>
                </div>
                <div class="widget-content">
                    <div id="action-status-chart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-12">
            <div class="widget">
                <div class="widget-heading">
                    <h5 id="secondary-chart-title">Analisis Area / Perusahaan</h5>
                </div>
                <div class="widget-content">
                    <div id="secondary-chart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row mb-4 g-3">
        <div class="col-xl-6 col-lg-12">
            <div class="widget">
                <div class="widget-heading">
                    <h5>Top 5 Pelanggaran CLSR</h5>
                </div>
                <div class="widget-content p-0">
                    <div id="clsr-violations-container" class="table-responsive">
                        <!-- JS Content -->
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-12">
            <div class="widget">
                <div class="widget-heading">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-tasks me-2 text-primary"></i>
                        <h5 class="mb-0">Action Items Saya (Pending)</h5>
                    </div>
                </div>
                <div class="widget-content p-0">
                    <div id="user-actions-container" class="table-responsive">
                         <!-- JS Content -->
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('plugins/src/apex/apexcharts.min.js')); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // Inisialisasi Select2
    $(document).ready(function() {
        if ($('.select2-company').length) {
            $('.select2-company').select2({
                placeholder: "Pilih Perusahaan",
                allowClear: true,
                width: 'resolve'
            });
        }

        // Load data pertama kali
        refreshDashboard();
    });

    let charts = {};

    function refreshDashboard() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        // Ambil ID company, atau string kosong jika "Semua Perusahaan" atau elemen tidak ada (user biasa)
        const companyId = document.getElementById('company_id')?.value || '';

        // Update Text Periode
        const dateOpt = { day: 'numeric', month: 'short', year: 'numeric' };
        document.getElementById('period-display').innerText =
            `${new Date(startDate).toLocaleDateString('id-ID', dateOpt)} s/d ${new Date(endDate).toLocaleDateString('id-ID', dateOpt)}`;

        showLoading();

        // AJAX Request
        fetch('<?php echo e(route("dashboard.analytics.data")); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                start_date: startDate,
                end_date: endDate,
                company_id: companyId
            })
        })
        .then(res => {
            if (!res.ok) throw new Error("Gagal mengambil data");
            return res.json();
        })
        .then(data => {
            updateKPIs(data.kpis);
            renderTrendChart(data.trendData);
            renderClassificationChart(data.classificationData);
            renderActionStatusChart(data.actionStatusData);
            // Pass chart type untuk mengubah judul chart sekunder (Company vs Area)
            renderSecondaryChart(data.secondaryChartData, data.secondaryChartType);
            renderClsrViolations(data.clsrViolations);
            renderUserActions(data.userActionItems);
        })
        .catch(err => {
            console.error(err);
            // alert("Terjadi kesalahan saat memuat data dashboard.");
        });
    }

    // Helper: Loading State
    function showLoading() {
        const loader = '<div class="spinner-border spinner-border-sm text-primary"></div>';
        ['total-reports', 'closure-rate', 'overdue-actions', 'close-out-progress'].forEach(id => {
            document.getElementById(id).innerHTML = loader;
        });
    }

    // Update KPI Numbers
    function updateKPIs(kpis) {
        if(!kpis) return;
        document.getElementById('total-reports').innerText = kpis.totalReports;
        document.getElementById('closure-rate').innerText = kpis.closureRate;
        document.getElementById('overdue-actions').innerText = kpis.overdueActions;
        document.getElementById('close-out-progress').innerText = kpis.closeOutProgress;
    }

    // --- CHART FUNCTIONS ---

    function renderTrendChart(data) {
        if(!data) return;
        const options = {
            chart: { type: 'area', height: 350, fontFamily: 'Nunito, sans-serif', toolbar: {show: false} },
            series: [{ name: 'Jumlah Laporan', data: data.data }],
            xaxis: { categories: data.labels },
            colors: ['#4361ee'],
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.1, stops: [0, 90, 100] } },
            dataLabels: { enabled: false },
            grid: { borderColor: '#e2e8f0', strokeDashArray: 4 }
        };
        if(charts.trend) charts.trend.destroy();
        charts.trend = new ApexCharts(document.querySelector("#trend-chart"), options);
        charts.trend.render();
    }

    function renderClassificationChart(data) {
        if(!data) return;

        // Gunakan ECharts untuk chart ini agar bisa menampilkan garis penunjuk (leader lines)
        if(charts.class && typeof charts.class.dispose === 'function') {
            charts.class.dispose();
        } else if(charts.class && typeof charts.class.destroy === 'function') {
            charts.class.destroy();
        }

        var chartDom = document.getElementById('classification-chart');
        var myChart = echarts.init(chartDom);
        charts.class = myChart;

        let pieData = [];
        let total = 0;
        for(let i=0; i<data.labels.length; i++) {
            // Jika nilai 0, sembunyikan label dan garisnya agar tidak menumpuk
            let itemLabelShow = data.data[i] > 0;
            pieData.push({
                name: data.labels[i],
                value: data.data[i],
                label: { show: itemLabelShow },
                labelLine: { show: itemLabelShow }
            });
            total += data.data[i];
        }

        let isDark = document.body.classList.contains('dark');
        let textColor = isDark ? '#f1f5f9' : '#0f172a';
        let borderColor = isDark ? '#1e293b' : '#fff';

        const options = {
            tooltip: {
                trigger: 'item',
                formatter: '{b}: {c} ({d}%)',
                textStyle: { fontFamily: 'Nunito, sans-serif' }
            },
            legend: {
                bottom: '0%',
                left: 'center',
                textStyle: { fontFamily: 'Nunito, sans-serif', color: textColor },
                itemWidth: 14,
                itemHeight: 14
            },
            color: ['#3b82f6', '#f59e0b', '#0ea5e9', '#ef4444', '#10b981'], // Action, Condition, Near Miss, Incident, Positive
            series: [
                {
                    name: 'Klasifikasi',
                    type: 'pie',
                    radius: ['35%', '50%'], // Diperkecil lagi agar label punya ruang gerak maksimal
                    center: ['50%', '42%'], 
                    avoidLabelOverlap: true,
                    itemStyle: {
                        borderRadius: 5,
                        borderColor: borderColor,
                        borderWidth: 2
                    },
                    label: {
                        show: true,
                        formatter: '{b}\n{c} ({d}%)',
                        fontFamily: 'Nunito, sans-serif',
                        fontSize: 11,
                        color: textColor,
                        lineHeight: 14
                    },
                    labelLine: {
                        show: true,
                        length: 20,    // Diperpanjang agar garis bisa menyebar
                        length2: 25,
                        smooth: 0.2,   // Dibuat agak melengkung agar lebih natural menghindari tabrakan
                        lineStyle: {
                            color: textColor
                        }
                    },
                    data: pieData
                }
            ],
            graphic: {
                type: 'text',
                left: 'center',
                top: '40%',
                style: {
                    text: 'Total\n' + total,
                    textAlign: 'center',
                    fill: textColor,
                    fontSize: 16,
                    fontWeight: 'bold',
                    fontFamily: 'Nunito, sans-serif'
                }
            }
        };

        myChart.setOption(options);

        // Auto resize ECharts
        window.addEventListener('resize', function() {
            myChart.resize();
        });
    }

    function renderActionStatusChart(data) {
        if(!data) return;
        const options = {
            chart: { type: 'bar', height: 350, fontFamily: 'Nunito, sans-serif', toolbar: {show: false} },
            series: [{ name: 'Jumlah', data: data.data }],
            xaxis: { categories: data.labels },
            // Colors: Completed (Green), In Progress (Blue), To Do (Yellow), Overdue (Red)
            colors: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
            plotOptions: { bar: { distributed: true, borderRadius: 6, columnWidth: '45%' } },
            legend: { show: false },
            grid: { borderColor: '#e2e8f0', strokeDashArray: 4 }
        };
        if(charts.status) charts.status.destroy();
        charts.status = new ApexCharts(document.querySelector("#action-status-chart"), options);
        charts.status.render();
    }

    function renderSecondaryChart(data, type) {
        if(!data) return;

        // Update Title Dinamis (Top Perusahaan atau Top Area)
        const titleEl = document.getElementById('secondary-chart-title');
        titleEl.innerText = type === 'company' ? 'Top 10 Perusahaan Kontributor' : 'Top 10 Area Temuan';

        const options = {
            chart: { type: 'bar', height: 350, fontFamily: 'Nunito, sans-serif', toolbar: {show: false} },
            series: [{ name: 'Total Laporan', data: data.data }],
            xaxis: { categories: data.labels },
            colors: ['#805dca'],
            plotOptions: { bar: { borderRadius: 4, horizontal: true, barHeight: '60%' } },
            dataLabels: { enabled: true, textAnchor: 'start', style: { colors: ['#fff'] }, offsetX: 0 },
            grid: { borderColor: '#e2e8f0', strokeDashArray: 4 }
        };
        if(charts.secondary) charts.secondary.destroy();
        charts.secondary = new ApexCharts(document.querySelector("#secondary-chart"), options);
        charts.secondary.render();
    }

    // --- TABLE FUNCTIONS ---

    function renderClsrViolations(data) {
        const container = document.getElementById('clsr-violations-container');
        if(!data || data.length === 0) {
            container.innerHTML = '<div class="p-5 text-center text-muted">Tidak ada data pelanggaran pada periode ini.</div>';
            return;
        }
        let html = '<table class="table table-hover mb-0"><thead><tr><th class="ps-4">Kode</th><th>Deskripsi</th><th class="text-end pe-4">Jumlah</th></tr></thead><tbody>';
        data.forEach(item => {
            html += `<tr>
                <td class="ps-4"><span class="badge bg-light-danger text-danger border border-danger">${item.sequence_number}</span></td>
                <td class="text-truncate" style="max-width: 250px;">${item.name}</td>
                <td class="text-end pe-4 fw-bold text-dark">${item.count}</td>
            </tr>`;
        });
        html += '</tbody></table>';
        container.innerHTML = html;
    }

    function renderUserActions(data) {
        const container = document.getElementById('user-actions-container');
        if(!data || data.length === 0) {
            container.innerHTML = '<div class="p-5 text-center text-muted"><i class="fas fa-check-circle fa-2x mb-3 text-success"></i><br>Tidak ada Action Item pending. Great job!</div>';
            return;
        }
        let html = '<table class="table table-hover mb-0"><thead><tr><th class="ps-4">Laporan</th><th>Task</th><th class="text-end pe-4">Due Date</th></tr></thead><tbody>';
        data.forEach(item => {
            const dateClass = item.is_overdue ? 'text-danger fw-bold' : 'text-muted';
            const badge = item.is_overdue ? '<span class="badge bg-danger ms-1">Overdue</span>' : '';
            html += `<tr>
                <td class="ps-4">
                    <div class="fw-bold text-dark" style="font-size:13px;">${item.report_number}</div>
                    <span class="badge bg-light-secondary text-secondary" style="font-size:10px;">${item.category_code}</span>
                </td>
                <td>
                    <div class="text-truncate text-muted" style="max-width: 220px;" title="${item.description}">${item.description}</div>
                </td>
                <td class="text-end pe-4" style="vertical-align: middle;">
                    <div class="${dateClass}" style="font-size:12px;">${item.target_date}</div>
                    ${badge}
                </td>
            </tr>`;
        });
        html += '</tbody></table>';
        container.innerHTML = html;
    }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/dashboard/cermat.blade.php ENDPATH**/ ?>