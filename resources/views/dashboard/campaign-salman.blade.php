@extends('layouts.app')

@section('title', 'Dashboard Campaign Salman - CERMAT Analytics')

{{-- STYLES --}}
@push('styles')
<link href="{{ asset('plugins/src/apex/apexcharts.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets/css/light/dashboard/dash_2.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/dark/dashboard/dash_2.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/light/components/list-group.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets/css/dark/components/list-group.css') }}" rel="stylesheet" type="text/css">
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

    /* Table Styling */
    .table-custom {
        font-size: 13px;
    }
    .table-custom thead th {
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0;
    }
    .table-custom tbody tr {
        transition: background-color 0.2s;
    }
    .table-custom tbody tr:hover {
        background-color: #f8fafc;
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
    body.dark .table-custom thead th {
        background: #0f172a;
        color: #94a3b8;
    }
    body.dark .table-custom tbody tr:hover {
        background-color: #0f172a;
    }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">

    {{-- HEADER & FILTERS --}}
    <div class="row layout-top-spacing mb-4">
        <div class="col-12">
            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">

                {{-- Title --}}
                <div>
                    <h3 class="fw-bold mb-1">
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        Dashboard Campaign Salman
                    </h3>
                    <p class="text-muted mb-0">
                        Periode Data:
                        <span class="text-primary fw-bold" id="period-display">Loading...</span>
                    </p>
                </div>

                {{-- Filter Bar --}}
                <div class="filter-container d-flex flex-wrap gap-3 align-items-end">

                    {{-- Filter Perusahaan (Hanya Super Admin & HSSE) --}}
                    @if(auth()->user()->hasAnyRole(['super-admin', 'hsse']))
                    <div class="filter-group">
                        <label class="filter-label">Perusahaan</label>
                        <select id="company_id" class="form-select form-select-sm select2-company" style="width: 220px;">
                            <option value="">Semua Perusahaan</option>
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Filter Entity Function (Conditional) --}}
                    @if($entityFunctions->isNotEmpty())
                    <div class="filter-group">
                        <label class="filter-label">Fungsi Entitas</label>
                        <select id="entity_function_id" class="form-select form-select-sm select2-entity" style="width: 220px;">
                            <option value="">Semua Fungsi</option>
                            @foreach($entityFunctions as $function)
                            <option value="{{ $function->id }}">{{ $function->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="filter-group">
                        <label class="filter-label">Tanggal Mulai</label>
                        <input type="date" id="start_date" class="form-control form-control-sm"
                               value="{{ \Carbon\Carbon::now()->startOfYear()->format('Y-m-d') }}">
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Tanggal Akhir</label>
                        <input type="date" id="end_date" class="form-control form-control-sm"
                               value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
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

    {{-- KPI SECTION --}}
    <div class="row mb-4 g-3">
        <!-- Total Campaigns -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="kpi-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="kpi-value text-primary" id="total-campaigns">-</div>
                        <div class="kpi-label">Total Kampanye</div>
                    </div>
                    <div class="kpi-icon-wrapper bg-light-primary text-primary">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                </div>
                <div class="kpi-subtext text-muted">Kampanye Terlaksana</div>
            </div>
        </div>

        <!-- Total Participants -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="kpi-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="kpi-value text-success" id="total-participants">-</div>
                        <div class="kpi-label">Total Peserta</div>
                    </div>
                    <div class="kpi-icon-wrapper bg-light-success text-success">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="kpi-subtext text-success">Partisipasi Aktif</div>
            </div>
        </div>

        <!-- Average Participants -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="kpi-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="kpi-value text-info" id="avg-participants">-</div>
                        <div class="kpi-label">Rata-rata Peserta</div>
                    </div>
                    <div class="kpi-icon-wrapper bg-light-info text-info">
                        <i class="fas fa-user-friends"></i>
                    </div>
                </div>
                <div class="kpi-subtext text-info">Per Kampanye</div>
            </div>
        </div>

        <!-- Total Documents -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="kpi-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="kpi-value text-warning" id="total-documents">-</div>
                        <div class="kpi-label">Total Dokumen</div>
                    </div>
                    <div class="kpi-icon-wrapper bg-light-warning text-warning">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
                <div class="kpi-subtext text-warning">Dokumentasi & Absensi</div>
            </div>
        </div>
    </div>

    {{-- CHARTS ROW 1 --}}
    <div class="row mb-4 g-3">
        <div class="col-xl-8 col-lg-12">
            <div class="widget">
                <div class="widget-heading">
                    <h5><i class="fas fa-chart-area me-2 text-primary"></i>Tren Kampanye Bulanan</h5>
                </div>
                <div class="widget-content">
                    <div id="trend-chart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-12">
            <div class="widget">
                <div class="widget-heading">
                    <h5><i class="fas fa-users me-2 text-success"></i>Statistik Peserta</h5>
                </div>
                <div class="widget-content">
                    <div id="participant-chart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- CHARTS ROW 2 --}}
    <div class="row mb-4 g-3">
        <div class="col-xl-6 col-lg-12">
            <div class="widget">
                <div class="widget-heading">
                    <h5><i class="fas fa-map-marker-alt me-2 text-danger"></i>Top 10 Lokasi Kampanye</h5>
                </div>
                <div class="widget-content">
                    <div id="location-chart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-12">
            <div class="widget">
                <div class="widget-heading">
                    <h5><i class="fas fa-chalkboard-teacher me-2 text-info"></i>Top 10 Pemateri</h5>
                </div>
                <div class="widget-content">
                    <div id="speaker-chart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- DISTRIBUTION CHART (Conditional) --}}
    <div class="row mb-4 g-3" id="distribution-row" style="display: none;">
        <div class="col-xl-12 col-lg-12">
            <div class="widget">
                <div class="widget-heading">
                    <h5 id="distribution-title"><i class="fas fa-chart-pie me-2 text-primary"></i>Distribusi</h5>
                </div>
                <div class="widget-content">
                    <div id="distribution-chart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- RECENT CAMPAIGNS TABLE --}}
    <div class="row mb-4 g-3">
        <div class="col-xl-12 col-lg-12">
            <div class="widget">
                <div class="widget-heading">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-history me-2 text-primary"></i>
                        <h5 class="mb-0">10 Kampanye Terbaru</h5>
                    </div>
                </div>
                <div class="widget-content p-0">
                    <div id="recent-campaigns-container" class="table-responsive">
                        <!-- JS Content -->
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/src/apex/apexcharts.min.js') }}"></script>
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

        if ($('.select2-entity').length) {
            $('.select2-entity').select2({
                placeholder: "Pilih Fungsi Entitas",
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
        const companyId = document.getElementById('company_id')?.value || '';
        const entityFunctionId = document.getElementById('entity_function_id')?.value || '';

        // Update Text Periode
        const dateOpt = { day: 'numeric', month: 'short', year: 'numeric' };
        document.getElementById('period-display').innerText =
            `${new Date(startDate).toLocaleDateString('id-ID', dateOpt)} s/d ${new Date(endDate).toLocaleDateString('id-ID', dateOpt)}`;

        showLoading();

        // AJAX Request
        fetch('{{ route("dashboard.campaign-salman.data") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                start_date: startDate,
                end_date: endDate,
                company_id: companyId,
                entity_function_id: entityFunctionId
            })
        })
        .then(res => {
            if (!res.ok) throw new Error("Gagal mengambil data");
            return res.json();
        })
        .then(data => {
            updateKPIs(data.kpis);
            renderTrendChart(data.trendData);
            renderParticipantChart(data.participantStats);
            renderLocationChart(data.topLocations);
            renderSpeakerChart(data.topSpeakers);
            renderDistributionChart(data);
            renderRecentCampaigns(data.recentCampaigns);
        })
        .catch(err => {
            console.error(err);
            alert("Terjadi kesalahan saat memuat data dashboard.");
        });
    }

    // Helper: Loading State
    function showLoading() {
        const loader = '<div class="spinner-border spinner-border-sm text-primary"></div>';
        ['total-campaigns', 'total-participants', 'avg-participants', 'total-documents'].forEach(id => {
            document.getElementById(id).innerHTML = loader;
        });
    }

    // Update KPI Numbers
    function updateKPIs(kpis) {
        if(!kpis) return;
        document.getElementById('total-campaigns').innerText = kpis.totalCampaigns.toLocaleString('id-ID');
        document.getElementById('total-participants').innerText = kpis.totalParticipants.toLocaleString('id-ID');
        document.getElementById('avg-participants').innerText = kpis.avgParticipants;
        document.getElementById('total-documents').innerText = kpis.totalDocuments.toLocaleString('id-ID');
    }

    // --- CHART FUNCTIONS ---

    function renderTrendChart(data) {
        if(!data) return;
        const options = {
            chart: { type: 'area', height: 350, fontFamily: 'Nunito, sans-serif', toolbar: {show: false} },
            series: [{ name: 'Jumlah Kampanye', data: data.data }],
            xaxis: { categories: data.labels },
            colors: ['#4361ee'],
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.1, stops: [0, 90, 100] } },
            dataLabels: { enabled: false },
            grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + " kampanye"
                    }
                }
            }
        };
        if(charts.trend) charts.trend.destroy();
        charts.trend = new ApexCharts(document.querySelector("#trend-chart"), options);
        charts.trend.render();
    }

    function renderParticipantChart(data) {
        if(!data) return;
        const options = {
            chart: { type: 'bar', height: 350, fontFamily: 'Nunito, sans-serif', toolbar: {show: false} },
            series: [{ name: 'Total Peserta', data: data.data }],
            xaxis: { categories: data.labels },
            colors: ['#10b981'],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '60%' } },
            dataLabels: { enabled: false },
            grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val.toLocaleString('id-ID') + " peserta"
                    }
                }
            }
        };
        if(charts.participant) charts.participant.destroy();
        charts.participant = new ApexCharts(document.querySelector("#participant-chart"), options);
        charts.participant.render();
    }

    function renderLocationChart(data) {
        if(!data || data.data.length === 0) {
            document.getElementById('location-chart').innerHTML =
                '<div class="text-center text-muted py-5">Tidak ada data lokasi</div>';
            return;
        }
        const options = {
            chart: { type: 'bar', height: 350, fontFamily: 'Nunito, sans-serif', toolbar: {show: false} },
            series: [{ name: 'Jumlah Kampanye', data: data.data }],
            xaxis: { categories: data.labels },
            colors: ['#ef4444'],
            plotOptions: { bar: { borderRadius: 4, horizontal: true, barHeight: '70%' } },
            dataLabels: { enabled: true, textAnchor: 'start', style: { colors: ['#fff'] }, offsetX: 0 },
            grid: { borderColor: '#e2e8f0', strokeDashArray: 4 }
        };
        if(charts.location) charts.location.destroy();
        charts.location = new ApexCharts(document.querySelector("#location-chart"), options);
        charts.location.render();
    }

    function renderSpeakerChart(data) {
        if(!data || data.data.length === 0) {
            document.getElementById('speaker-chart').innerHTML =
                '<div class="text-center text-muted py-5">Tidak ada data pemateri</div>';
            return;
        }
        const options = {
            chart: { type: 'bar', height: 350, fontFamily: 'Nunito, sans-serif', toolbar: {show: false} },
            series: [{ name: 'Jumlah Kampanye', data: data.data }],
            xaxis: { categories: data.labels },
            colors: ['#3b82f6'],
            plotOptions: { bar: { borderRadius: 4, horizontal: true, barHeight: '70%' } },
            dataLabels: { enabled: true, textAnchor: 'start', style: { colors: ['#fff'] }, offsetX: 0 },
            grid: { borderColor: '#e2e8f0', strokeDashArray: 4 }
        };
        if(charts.speaker) charts.speaker.destroy();
        charts.speaker = new ApexCharts(document.querySelector("#speaker-chart"), options);
        charts.speaker.render();
    }

    function renderDistributionChart(data) {
        let chartData = null;
        let title = '';

        if (data.companyDistribution) {
            chartData = data.companyDistribution;
            title = '<i class="fas fa-building me-2 text-primary"></i>Distribusi Per Perusahaan';
        } else if (data.entityFunctionDistribution) {
            chartData = data.entityFunctionDistribution;
            title = '<i class="fas fa-sitemap me-2 text-primary"></i>Distribusi Per Fungsi Entitas';
        }

        if (!chartData || chartData.data.length === 0) {
            document.getElementById('distribution-row').style.display = 'none';
            return;
        }

        document.getElementById('distribution-row').style.display = 'block';
        document.getElementById('distribution-title').innerHTML = title;

        const options = {
            chart: { type: 'donut', height: 350, fontFamily: 'Nunito, sans-serif' },
            series: chartData.data,
            labels: chartData.labels,
            colors: ['#4361ee', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#06b6d4', '#84cc16'],
            legend: { position: 'bottom', fontSize: '13px' },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                fontSize: '16px',
                                fontWeight: 700
                            }
                        }
                    }
                }
            },
            stroke: { show: true, width: 2, colors: ['#fff'] },
            dataLabels: {
                enabled: true,
                formatter: function(val, opts) {
                    return opts.w.config.series[opts.seriesIndex]
                }
            }
        };
        if(charts.distribution) charts.distribution.destroy();
        charts.distribution = new ApexCharts(document.querySelector("#distribution-chart"), options);
        charts.distribution.render();
    }

    // --- TABLE FUNCTION ---

    function renderRecentCampaigns(data) {
        const container = document.getElementById('recent-campaigns-container');
        if(!data || data.length === 0) {
            container.innerHTML = '<div class="p-5 text-center text-muted">Tidak ada data kampanye pada periode ini.</div>';
            return;
        }

        let html = `<table class="table table-hover table-custom mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Tanggal</th>
                    <th>Tema</th>
                    <th>Lokasi</th>
                    <th>Pemateri</th>
                    <th class="text-center">Peserta</th>
                    <th>Perusahaan</th>
                    <th class="text-end pe-4">Fungsi</th>
                </tr>
            </thead>
            <tbody>`;

        data.forEach(item => {
            html += `<tr>
                <td class="ps-4">
                    <span class="badge bg-light-primary text-primary">${item.tanggal}</span>
                </td>
                <td>
                    <div class="fw-bold text-dark" style="font-size:13px;">${item.tema}</div>
                </td>
                <td class="text-muted">${item.lokasi}</td>
                <td class="text-muted">${item.pemateri}</td>
                <td class="text-center">
                    <span class="badge bg-success">${item.peserta} orang</span>
                </td>
                <td class="text-muted" style="font-size:12px;">${item.company_name}</td>
                <td class="text-end pe-4 text-muted" style="font-size:11px;">${item.entity_function_name}</td>
            </tr>`;
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    }
</script>
@endpush
