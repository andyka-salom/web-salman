@extends('layouts.app')
@section('title', 'Dashboard Crew Assessment')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
:root {
    --ca-navy:#0f2d56; --ca-orange:#e07b00; --ca-green:#16a34a;
    --ca-amber:#d97706; --ca-red:#dc2626; --ca-slate:#64748b; --ca-r:12px;
    --ca-sh:0 1px 3px rgba(0,0,0,.07),0 4px 16px rgba(0,0,0,.05);
}
.kpi-card {
    background:var(--card-bg); border:1px solid var(--card-border-color);
    border-radius:var(--ca-r); padding:1.25rem 1.4rem;
    display:flex; align-items:center; gap:1rem;
    box-shadow:var(--ca-sh); transition:transform .18s,box-shadow .18s;
    position:relative; overflow:hidden; text-decoration:none; color:inherit;
}
.kpi-card::before { content:''; position:absolute; top:0;left:0; width:4px; height:100%; background:var(--kc,#1a56db); border-radius:var(--ca-r) 0 0 var(--ca-r); }
.kpi-card:hover { transform:translateY(-2px); box-shadow:0 6px 24px rgba(0,0,0,.1); color:inherit; }
.kpi-icon { width:48px;height:48px;border-radius:10px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:1.4rem; }
.kpi-lbl { font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#6b7280;margin-bottom:.15rem; }
.kpi-val { font-size:1.9rem;font-weight:800;line-height:1; }
.kpi-sub { font-size:.73rem;color:#9ca3af;margin-top:.2rem; }
.panel { background:var(--card-bg);border:1px solid var(--card-border-color);border-radius:var(--ca-r);box-shadow:var(--ca-sh);overflow:hidden;height:100%; }
.panel-hdr { padding:.8rem 1.2rem;border-bottom:1px solid var(--card-border-color);display:flex;align-items:center;justify-content:space-between;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--ca-slate); }
.panel-body { padding:1.1rem; }
.pend-row { display:flex;align-items:center;gap:.7rem;padding:.55rem 0;border-bottom:1px dashed var(--card-border-color); }
.pend-row:last-child { border-bottom:none; }
.pend-av { width:34px;height:34px;border-radius:50%;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;flex-shrink:0; }
.rec-row { display:grid;grid-template-columns:2fr 1.5fr 1fr 1fr 0.8fr;gap:.4rem;align-items:center;padding:.5rem .3rem;border-bottom:1px solid var(--card-border-color);font-size:.8rem; }
.rec-row:last-child { border-bottom:none; }
.rec-hdr { font-weight:700;font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af; }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
<div class="middle-content container-xxl p-0">

    {{-- Header --}}
    <div class="row layout-top-spacing mb-4 align-items-center">
        <div class="col-md-7">
            <h3 class="m-0 fw-bold">Dashboard Crew Assessment</h3>
            <p class="text-muted mb-0 small">Ringkasan hasil assessment kru kapal — {{ now()->format('d M Y H:i') }}</p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end flex-wrap">
            <a href="{{ route('crew-assessment.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-table me-1"></i> Semua Data</a>
            @can('manage crew assessment')
            <a href="{{ route('crew-assessment.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> Tambah</a>
            @endcan
        </div>
    </div>

    {{-- KPI --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <a href="{{ route('crew-assessment.index') }}" class="kpi-card" style="--kc:#0f2d56">
                <div class="kpi-icon" style="background:#e0e7ff;color:#0f2d56"><i class="bi bi-journal-check"></i></div>
                <div><div class="kpi-lbl">Total</div><div class="kpi-val">{{ number_format($totalAll) }}</div><div class="kpi-sub">semua record</div></div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('crew-assessment.index') }}?result=Lulus" class="kpi-card" style="--kc:#16a34a">
                <div class="kpi-icon" style="background:#dcfce7;color:#16a34a"><i class="bi bi-patch-check-fill"></i></div>
                <div><div class="kpi-lbl">Lulus</div><div class="kpi-val">{{ number_format($totalLulus) }}</div><div class="kpi-sub">assessment lulus</div></div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('crew-assessment.index') }}?result=Pending" class="kpi-card" style="--kc:#d97706">
                <div class="kpi-icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-hourglass-split"></i></div>
                <div><div class="kpi-lbl">Pending</div><div class="kpi-val">{{ number_format($totalPending) }}</div><div class="kpi-sub">menunggu keputusan</div></div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <div class="kpi-card" style="--kc:#1a56db">
                <div class="kpi-icon" style="background:#dbeafe;color:#1a56db"><i class="bi bi-calendar-check"></i></div>
                <div><div class="kpi-lbl">Bulan Ini</div><div class="kpi-val">{{ number_format($thisMonth) }}</div><div class="kpi-sub">{{ now()->format('M Y') }}</div></div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row g-3 mb-4">
        {{-- Monthly Trend --}}
        <div class="col-xl-8">
            <div class="panel">
                <div class="panel-hdr"><span><i class="bi bi-bar-chart-line me-1"></i> Tren Assessment — 12 Bulan Terakhir</span></div>
                <div class="panel-body" style="height:260px"><canvas id="chartTrend"></canvas></div>
            </div>
        </div>
        {{-- Hasil Donut --}}
        <div class="col-xl-4">
            <div class="panel">
                <div class="panel-hdr"><span><i class="bi bi-pie-chart me-1"></i> Distribusi Hasil</span></div>
                <div class="panel-body d-flex flex-column align-items-center justify-content-center" style="height:260px">
                    <canvas id="chartHasil" style="max-height:180px;max-width:180px"></canvas>
                    <div class="d-flex flex-wrap gap-2 mt-3 justify-content-center" style="font-size:.77rem">
                        @foreach($byResult as $br)
                        <span><span class="badge {{ $br->result === 'Lulus' ? 'bg-success' : ($br->result === 'Pending' ? 'bg-warning text-dark' : 'bg-danger') }} me-1">&nbsp;</span>{{ $br->result ?? '—' }} ({{ $br->total }})</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MEV + Lokasi + Jabatan --}}
    <div class="row g-3 mb-4">
        {{-- Per MEV --}}
        <div class="col-xl-4">
            <div class="panel">
                <div class="panel-hdr"><span><i class="bi bi-tags me-1"></i> Per MEV Type</span></div>
                <div class="panel-body" style="height:240px"><canvas id="chartMev"></canvas></div>
            </div>
        </div>
        {{-- Per Lokasi --}}
        <div class="col-xl-4">
            <div class="panel">
                <div class="panel-hdr"><span><i class="bi bi-geo-alt me-1"></i> Per Lokasi</span></div>
                <div class="panel-body" style="height:240px"><canvas id="chartLokasi"></canvas></div>
            </div>
        </div>
        {{-- Per Jabatan --}}
        <div class="col-xl-4">
            <div class="panel">
                <div class="panel-hdr"><span><i class="bi bi-person-badge me-1"></i> Per Jabatan (Top 10)</span></div>
                <div class="panel-body" style="height:240px;overflow:hidden"><canvas id="chartJabatan"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Pending List + Recent --}}
    <div class="row g-3 mb-4">
        {{-- Pending --}}
        <div class="col-xl-4">
            <div class="panel">
                <div class="panel-hdr">
                    <span><i class="bi bi-hourglass text-warning me-1"></i> Assessment Pending
                        @if($totalPending > 0)<span class="badge bg-warning text-dark ms-1" style="font-size:.65rem">{{ $totalPending }}</span>@endif
                    </span>
                    @if($totalPending > 10)<a href="{{ route('crew-assessment.index') }}?result=Pending" class="text-primary" style="font-size:.73rem">Semua</a>@endif
                </div>
                <div class="panel-body" style="height:300px;overflow-y:auto">
                    @forelse($pendingList as $p)
                    <div class="pend-row">
                        <div class="pend-av">{{ strtoupper(substr($p->display_crew_name,0,2)) }}</div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-semibold text-truncate" style="font-size:.83rem">{{ $p->display_crew_name }}</div>
                            <div class="text-muted text-truncate" style="font-size:.73rem">
                                {{ $p->position_proposed }} · {{ $p->display_vessel_name }}
                            </div>
                        </div>
                        <div class="text-end flex-shrink-0" style="font-size:.72rem;color:#9ca3af">{{ $p->assessment_date->format('d/m/Y') }}</div>
                        <a href="{{ route('crew-assessment.show', $p->id) }}" class="btn btn-sm btn-outline-secondary flex-shrink-0" style="padding:.15rem .4rem"><i class="bi bi-eye"></i></a>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-check-circle-fill text-success fs-2 d-block mb-2"></i>
                        <div class="small">Tidak ada assessment pending.</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Recent --}}
        <div class="col-xl-8">
            <div class="panel">
                <div class="panel-hdr">
                    <span><i class="bi bi-clock me-1"></i> Terbaru</span>
                    <a href="{{ route('crew-assessment.index') }}" class="text-primary" style="font-size:.73rem">Lihat semua</a>
                </div>
                <div class="panel-body p-0">
                    <div class="px-3 py-2 border-bottom">
                        <div class="rec-row rec-hdr">
                            <span>Nama / Jabatan</span>
                            <span>Kapal</span>
                            <span>MEV / Lokasi</span>
                            <span>Tgl Assessment</span>
                            <span class="text-center">Hasil</span>
                        </div>
                    </div>
                    <div class="px-3" style="padding-bottom:.5rem">
                        @forelse($recentList as $r)
                        <a href="{{ route('crew-assessment.show', $r->id) }}" class="rec-row text-decoration-none text-reset" style="transition:background .1s" onmouseover="this.style.background='var(--card-border-color)'" onmouseout="this.style.background=''">
                            <div class="overflow-hidden">
                                <div class="fw-semibold text-truncate">{{ $r->display_crew_name }}</div>
                                <div class="text-muted text-truncate" style="font-size:.73rem">{{ $r->position_proposed ?? '—' }} · {{ $r->position_type ?? '' }}</div>
                            </div>
                            <div class="text-truncate">{{ $r->display_vessel_name }}</div>
                            <div>
                                <span class="badge bg-light text-dark border" style="font-size:.68rem">{{ $r->mev_type ?? '—' }}</span>
                                <div class="text-muted" style="font-size:.72rem">{{ $r->assessment_location ?? '—' }}</div>
                            </div>
                            <div class="text-muted">{{ $r->assessment_date->format('d/m/Y') }}</div>
                            <div class="text-center">
                                <span class="badge bg-{{ $r->result_color }}" style="font-size:.72rem">{{ $r->result ?? '—' }}</span>
                            </div>
                        </a>
                        @empty
                        <p class="text-muted small py-3 text-center mb-0">Belum ada data.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Per Company (admin only) --}}
    @if($canManage && $byCompany->isNotEmpty())
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="panel">
                <div class="panel-hdr"><span><i class="bi bi-building me-1"></i> Ringkasan per Perusahaan</span></div>
                <div class="panel-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" style="font-size:.82rem">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Perusahaan</th>
                                    <th class="text-center text-success">Lulus</th>
                                    <th class="text-center text-warning">Pending</th>
                                    <th class="text-center text-danger">Tidak Lulus</th>
                                    <th class="text-center fw-bold">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($byCompany as $cid => $rows)
                                @php
                                    $cn = $rows->first()->company?->name ?? '—';
                                    $cl = $rows->where('result','Lulus')->sum('total');
                                    $cp = $rows->where('result','Pending')->sum('total');
                                    $ct2= $rows->where('result','Tidak Lulus')->sum('total');
                                    $ct = $rows->sum('total');
                                @endphp
                                <tr>
                                    <td class="ps-4 fw-semibold">{{ $cn }}</td>
                                    <td class="text-center">@if($cl)<span class="badge bg-success">{{ $cl }}</span>@else<span class="text-muted">—</span>@endif</td>
                                    <td class="text-center">@if($cp)<span class="badge bg-warning text-dark">{{ $cp }}</span>@else<span class="text-muted">—</span>@endif</td>
                                    <td class="text-center">@if($ct2)<span class="badge bg-danger">{{ $ct2 }}</span>@else<span class="text-muted">—</span>@endif</td>
                                    <td class="text-center fw-bold">{{ $ct }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
    const dark  = document.body.dataset.theme === 'dark' || document.documentElement.classList.contains('dark');
    const grid  = dark ? 'rgba(255,255,255,.07)' : 'rgba(0,0,0,.06)';
    const text  = dark ? '#9ca3af' : '#6b7280';
    Chart.defaults.color = text;
    Chart.defaults.font.family = "'Nunito','DM Sans',sans-serif";

    // Monthly trend
    new Chart(document.getElementById('chartTrend'), {
        type:'bar',
        data:{
            labels: @json($trend->pluck('month')),
            datasets:[{ label:'Assessment', data:@json($trend->pluck('total')),
                backgroundColor:'rgba(15,45,86,.75)', borderRadius:5, borderSkipped:false }]
        },
        options:{ responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ display:false } },
            scales:{ x:{ grid:{ color:grid } }, y:{ grid:{ color:grid }, ticks:{ precision:0 }, beginAtZero:true } }
        }
    });

    // Hasil donut
    const hrLabels = @json($byResult->pluck('result'));
    const hrData   = @json($byResult->pluck('total'));
    const hrColors = hrLabels.map(l => l === 'Lulus' ? '#16a34a' : l === 'Pending' ? '#d97706' : '#dc2626');
    new Chart(document.getElementById('chartHasil'), {
        type:'doughnut',
        data:{ labels:hrLabels, datasets:[{ data:hrData, backgroundColor:hrColors, borderWidth:2, borderColor: dark?'#1e293b':'#fff' }] },
        options:{ responsive:true, maintainAspectRatio:false, cutout:'65%',
            plugins:{ legend:{ display:false } }
        }
    });

    const pal = ['#1a56db','#0891b2','#16a34a','#d97706','#dc2626','#7c3aed','#db2777','#0f766e','#b45309'];

    // MEV
    new Chart(document.getElementById('chartMev'), {
        type:'doughnut',
        data:{ labels:@json($byMev->pluck('mev_type')), datasets:[{ data:@json($byMev->pluck('total')), backgroundColor:pal, borderWidth:2, borderColor: dark?'#1e293b':'#fff' }] },
        options:{ responsive:true, maintainAspectRatio:false, cutout:'55%', plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, font:{ size:11 } } } } }
    });

    // Lokasi
    new Chart(document.getElementById('chartLokasi'), {
        type:'bar',
        data:{ labels:@json($byLocation->pluck('assessment_location')), datasets:[{ label:'Jumlah', data:@json($byLocation->pluck('total')), backgroundColor: @json($byLocation->pluck('assessment_location'))->map((_,i) => pal[i%pal.length]+'cc'), borderRadius:4 }] },
        options:{ responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ display:false } },
            scales:{ x:{ grid:{ display:false } }, y:{ grid:{ color:grid }, ticks:{ precision:0 }, beginAtZero:true } }
        }
    });

    // Jabatan
    new Chart(document.getElementById('chartJabatan'), {
        type:'bar', indexAxis:'y',
        data:{ labels:@json($byPosition->pluck('position_proposed')), datasets:[{ label:'Jumlah', data:@json($byPosition->pluck('total')), backgroundColor:'rgba(26,86,219,.7)', borderRadius:3 }] },
        options:{ responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ display:false } },
            scales:{ x:{ grid:{ color:grid }, ticks:{ precision:0 }, beginAtZero:true }, y:{ grid:{ display:false }, ticks:{ font:{ size:10 } } } }
        }
    });
}());
</script>
@endpush
