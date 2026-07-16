

<?php $__env->startSection('title', 'Dashboard KPI HSSE'); ?>

<?php $__env->startPush('styles'); ?>
    <style>
    /* ── Stat cards ───────────────────────────────────────────── */
    .stat-cards { display:flex;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem; }
    .stat-card { border-radius:12px;padding:.9rem 1.1rem;border:2px solid;min-width:110px;flex:1; }
    .stat-card .s-val { font-size:2rem;font-weight:800;line-height:1; }
    .stat-card .s-lbl { font-size:.7rem;margin-top:4px;font-weight:700;letter-spacing:.5px;opacity:.8; }

    /* ── Chart panel ──────────────────────────────────────────── */
    .dash-panel { background:#fff;border-radius:12px;border:1px solid #e5e7eb;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:hidden; }

    /* FIX: Force white text on all panel headers */
    .dash-panel-hdr {
        background: linear-gradient(135deg, #1e3a5f, #2563eb);
        padding: .75rem 1.1rem;
    }
    .dash-panel-hdr h5 {
        margin: 0;
        font-size: .93rem;
        font-weight: 700;
        color: #ffffff !important;
    }
    .dash-panel-hdr p {
        margin: 0;
        font-size: .72rem;
        color: rgba(255,255,255,.85) !important;
    }
    .dash-panel-hdr-gray {
        background: linear-gradient(135deg, #374151, #4b5563) !important;
    }

    .dash-panel-body { padding:1rem; }

    /* ── Pyramid ─────────────────────────────────────────────── */
    .pyramid-wrap { display:flex;flex-direction:column;align-items:center;gap:0;width:100%;max-width:480px;margin:0 auto; }
    .pyramid-row  { display:flex;align-items:stretch;width:100%;margin-bottom:2px; }
    .pyr-label  { width:88px;flex-shrink:0;display:flex;align-items:center;font-size:.7rem;font-weight:700;color:#374151;padding-right:6px;text-align:right;justify-content:flex-end;line-height:1.2; }
    .pyr-right  { flex:1;display:flex;align-items:center; }
    .pyr-bar    { border-radius:3px;display:flex;align-items:center;justify-content:space-between;padding:0 8px;min-height:30px;color:#fff;font-weight:700;font-size:.74rem;transition:width .5s; }
    .pyr-score  { font-size:.65rem;opacity:.85; }
    .pyramid-note { background:#fef3c7;border:1px solid #fde68a;border-radius:6px;padding:6px 12px;font-size:.73rem;color:#92400e;margin-top:8px;width:100%; }

    .leading-info { background:linear-gradient(135deg,#dbeafe,#eff6ff);border:2px solid #3b82f6;border-radius:8px;padding:10px 14px;margin-top:10px;width:100%; }
    .leading-info h6 { font-size:.77rem;font-weight:700;color:#1e3a8a;margin-bottom:6px; }
    .leading-row { display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px dashed #bfdbfe;font-size:.73rem; }
    .leading-row:last-child { border-bottom:none; }
    .formula-tag { display:inline-block;font-size:.63rem;background:#1e40af;color:#fff;border-radius:3px;padding:1px 5px;font-weight:700;margin-left:4px; }
    .formula-tag.avg { background:#0891b2; }

    /* ── Table ────────────────────────────────────────────────── */
    .recent-tbl { font-size:.84rem; }
    .badge-kpi-status { border-radius:20px;padding:3px 10px;font-size:.72rem;font-weight:700;display:inline-block; }
    .badge-status-draft     { background:#f1f5f9;color:#6b7280;border:1px solid #d6d8db; }
    .badge-status-submitted { background:#d0e9ff;color:#014c8c;border:1px solid #b8daff; }
    .badge-status-validated { background:#d1f3d1;color:#0e6245;border:1px solid #c3e6cb; }
    .badge-status-rejected  { background:#ffe0e0;color:#d93025;border:1px solid #f5c6cb; }
    .score-pill { display:inline-flex;align-items:center;justify-content:center;border-radius:50%;font-weight:700;border:3px solid; }
    .score-pill.excellent { border-color:#0e6245;color:#0e6245;background:#d1f3d1; }
    .score-pill.good      { border-color:#014c8c;color:#014c8c;background:#d0e9ff; }
    .score-pill.fair      { border-color:#965a00;color:#965a00;background:#fff4cc; }
    .score-pill.poor      { border-color:#d93025;color:#d93025;background:#ffe0e0; }
    .score-pill.none      { border-color:#9ca3af;color:#9ca3af;background:#f1f1f1; }

    /* ── Pagination ───────────────────────────────────────────── */
    .tbl-footer { display:flex;align-items:center;justify-content:space-between;padding:.65rem 1rem;border-top:1px solid #f1f5f9;flex-wrap:wrap;gap:.5rem; }
    .tbl-footer .pag-info { font-size:.75rem;color:#6b7280; }
    .pag-btns { display:flex;gap:4px;flex-wrap:wrap; }
    .pag-btn { border:1px solid #e5e7eb;background:#fff;color:#374151;border-radius:6px;padding:4px 10px;font-size:.75rem;cursor:pointer;transition:all .15s;line-height:1.4; }
    .pag-btn:hover:not(:disabled) { background:#f1f5f9;border-color:#cbd5e1; }
    .pag-btn.active { background:#2563eb;border-color:#2563eb;color:#fff;font-weight:700; }
    .pag-btn:disabled { opacity:.4;cursor:not-allowed; }

    /* ── Top companies ────────────────────────────────────────── */
    .top-row { display:flex;align-items:center;gap:.75rem;padding:.55rem 0;border-bottom:1px solid #f1f5f9; }
    .top-row:last-child { border-bottom:none; }
    .top-rank { width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.72rem;flex-shrink:0; }
    .top-rank.g1 { background:#fde68a;color:#92400e; }
    .top-rank.g2 { background:#e2e8f0;color:#475569; }
    .top-rank.g3 { background:#fed7aa;color:#c2410c; }
    .top-rank.gn { background:#f1f5f9;color:#6b7280; }
    .top-bar-wrap { flex:1;height:6px;background:#f1f5f9;border-radius:3px;overflow:hidden; }
    .top-bar-fill { height:100%;border-radius:3px;transition:width .5s; }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $monthNames = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agt',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
        $monthFull  = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    ?>

    <div class="layout-px-spacing">
    <div class="row layout-top-spacing">
    <div class="col-12">

        
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-bold mb-1">📊 Dashboard KPI HSSE</h2>
                <p class="text-muted mb-0" style="font-size:.84rem;">
                    <?php if($isHsse): ?> Semua Perusahaan Kontraktor
                    <?php else: ?> <strong><?php echo e(auth()->user()->company->name ?? ''); ?></strong>
                    <?php endif; ?>
                </p>
            </div>
            <a href="<?php echo e(route('kpi-hsse.index')); ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                📋 Daftar Laporan
            </a>
        </div>

        
        <form method="GET" class="row g-2 mb-4 align-items-end">
            <?php if($isHsse): ?>
                <div class="col-auto">
                    <label class="form-label fw-semibold mb-1" style="font-size:.78rem;">Perusahaan</label>
                    <select name="company_id" class="form-select form-select-sm" style="min-width:175px;">
                        <option value="">Semua</option>
                        <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($c->id); ?>" <?php echo e(request('company_id')==$c->id ? 'selected' : ''); ?>><?php echo e($c->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            <?php endif; ?>
            <div class="col-auto">
                <label class="form-label fw-semibold mb-1" style="font-size:.78rem;">Tahun</label>
                <select name="period_year" class="form-select form-select-sm">
                    <option value="">Semua Tahun</option>
                    <?php $__currentLoopData = $periods->pluck('period_year')->unique()->sortDesc(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($yr); ?>" <?php echo e(request('period_year')==$yr ? 'selected' : ''); ?>><?php echo e($yr); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label fw-semibold mb-1" style="font-size:.78rem;">Bulan</label>
                <select name="period_month" class="form-select form-select-sm">
                    <option value="">Semua Bulan</option>
                    <?php $__currentLoopData = $monthFull; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m => $mn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($m); ?>" <?php echo e(request('period_month')==$m ? 'selected' : ''); ?>><?php echo e($mn); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-auto"><button type="submit" class="btn btn-primary btn-sm">Filter</button></div>
            <div class="col-auto"><a href="<?php echo e(route('kpi-hsse.dashboard')); ?>" class="btn btn-outline-secondary btn-sm">Reset</a></div>
        </form>

        
        <div class="stat-cards">
            <div class="stat-card" style="background:#f0fdf4;border-color:#86efac;">
                <div class="s-val" style="color:#16a34a;"><?php echo e($stats['validated']); ?></div>
                <div class="s-lbl" style="color:#16a34a;">✅ VALIDATED</div>
            </div>
            <div class="stat-card" style="background:#dbeafe;border-color:#93c5fd;">
                <div class="s-val" style="color:#1d4ed8;"><?php echo e($stats['submitted']); ?></div>
                <div class="s-lbl" style="color:#1d4ed8;">⏳ SUBMITTED</div>
            </div>
            <div class="stat-card" style="background:#ffe0e0;border-color:#fca5a5;">
                <div class="s-val" style="color:#d93025;"><?php echo e($stats['rejected']); ?></div>
                <div class="s-lbl" style="color:#d93025;">❌ REJECTED</div>
            </div>
            <div class="stat-card" style="background:#fff4cc;border-color:#fde68a;">
                <div class="s-val" style="color:#92400e;"><?php echo e($stats['draft']); ?></div>
                <div class="s-lbl" style="color:#92400e;">📝 DRAFT</div>
            </div>
            <div class="stat-card" style="background:#f1f5f9;border-color:#cbd5e1;">
                <div class="s-val" style="color:#374151;"><?php echo e($stats['total']); ?></div>
                <div class="s-lbl" style="color:#374151;">📁 TOTAL</div>
            </div>
        </div>

        
        <div class="row g-4 mb-4">

            
            <div class="col-lg-7">
                <div class="dash-panel h-100">
                    <div class="dash-panel-hdr">
                        <h5>📊 KPI HSSE Score — Per Perusahaan</h5>
                        <p>Status: Validated | Filter: Perusahaan / Periode</p>
                    </div>
                    <div class="dash-panel-body" style="overflow-x:auto;">
                        <div id="bar-chart-wrap" style="min-width:360px;">
                            <canvas id="kpi-bar-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="col-lg-5">
                <div class="dash-panel h-100">
                    <div class="dash-panel-hdr">
                        <h5>🔺 Lagging &amp; Leading Indicator</h5>
                        <p>Total implementasi semua laporan validated</p>
                    </div>
                    <div class="dash-panel-body">

                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:.75rem;">
                            <div style="background:#c2410c;color:#fff;padding:2px 12px;border-radius:4px;font-size:.75rem;font-weight:700;letter-spacing:.5px;">LAGGING INDICATOR</div>
                        </div>

                        <?php
                            $pyrItems  = collect($laggingPyramidData)->sortBy('item_no');
                            $maxTotal  = max(1, $pyrItems->max('total_actual') ?: 1);
                            $pyrColors = ['#dc2626','#ea580c','#d97706','#ca8a04','#65a30d','#0d9488','#64748b','#475569'];
                            $pyrNames  = [1=>'FAT',2=>'LTI',3=>'RWDC',4=>'MTC',5=>'HIPO',6=>'FAC',7=>'NEARMISS',8=>'MANHOURS'];
                        ?>

                        <div class="pyramid-wrap">
                        <?php $__currentLoopData = $pyrItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $pyr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $sn    = $pyrNames[$pyr['item_no']] ?? ('No.'.$pyr['item_no']);
                                $color = $pyrColors[$idx] ?? '#64748b';
                                $pct   = max(20, round($pyr['total_actual'] / $maxTotal * 100));
                                $isAR  = !$pyr['is_scored'];
                            ?>
                            <div class="pyramid-row">
                                <div class="pyr-label"><?php echo e($sn); ?></div>
                                <div class="pyr-right">
                                    <div class="pyr-bar" style="width:<?php echo e($pct); ?>%;background:<?php echo e($color); ?>;">
                                        <span><?php echo e(number_format($pyr['total_actual'])); ?></span>
                                        <?php if(!$isAR && $pyr['avg_score'] > 0): ?>
                                            <span class="pyr-score">sc:<?php echo e(number_format($pyr['avg_score'],1)); ?></span>
                                        <?php else: ?>
                                            <span class="pyr-score" style="font-style:italic;opacity:.6;">A/R</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <?php $sumAll = collect($laggingPyramidData)->sum('total_actual'); ?>
                        <div class="pyramid-note">
                            <strong>∑ Total Lagging:</strong> <?php echo e(number_format($sumAll)); ?> implementasi
                        </div>

                        <div style="display:flex;align-items:center;gap:8px;margin-top:1rem;margin-bottom:.75rem;width:100%;">
                            <div style="background:#1d4ed8;color:#fff;padding:2px 12px;border-radius:4px;font-size:.75rem;font-weight:700;letter-spacing:.5px;">LEADING INDICATOR</div>
                        </div>

                        <div class="leading-info">
                            <h6>Rumus Penilaian Leading</h6>
                            <div class="leading-row">
                                <span><strong>Jumlah (∑)</strong> <span class="formula-tag">SUM</span></span>
                                <span style="color:#1e3a8a;font-size:.72rem;">No. 1, 7, 8, 9, 13</span>
                            </div>
                            <div class="leading-row">
                                <span><strong>Rata-rata (%)</strong> <span class="formula-tag avg">AVG</span></span>
                                <span style="color:#1e3a8a;font-size:.72rem;">No. 2, 3, 4, 5, 6, 10, 11, 12, 14, 15</span>
                            </div>
                            <div style="margin-top:8px;padding-top:6px;border-top:1px dashed #bfdbfe;font-size:.71rem;color:#1e40af;">
                                <strong>Total Score</strong> = Score Lagging + Score Leading
                            </div>
                        </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        
        <div class="row g-4 mb-4">

            <div class="col-lg-8">
                <div class="dash-panel h-100">
                    <div class="dash-panel-hdr">
                        <h5>📈 Trend Score 6 Bulan Terakhir</h5>
                        <p>Rata-rata score validated per periode</p>
                    </div>
                    <div class="dash-panel-body">
                        <canvas id="trend-chart" height="220"></canvas>
                    </div>
                </div>
            </div>

            <?php if($isHsse && $topCompanies->count() > 0): ?>
                <div class="col-lg-4">
                    <div class="dash-panel h-100">
                        <div class="dash-panel-hdr">
                            <h5>🏆 Top Performers</h5>
                            <p>Rata-rata score tertinggi (validated)</p>
                        </div>
                        <div class="dash-panel-body">
                            <?php $__currentLoopData = $topCompanies->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $co): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $rankCls = $i===0?'g1':($i===1?'g2':($i===2?'g3':'gn'));
                                    $avgSc   = round((float)$co->avg_score, 1);
                                    $barPct  = min(100, $avgSc);
                                    $barCol  = $avgSc>=90?'#16a34a':($avgSc>=75?'#2563eb':($avgSc>=60?'#d97706':'#dc2626'));
                                ?>
                                <div class="top-row">
                                    <div class="top-rank <?php echo e($rankCls); ?>"><?php echo e($i+1); ?></div>
                                    <div style="flex:1;min-width:0;">
                                        <div style="font-size:.78rem;font-weight:700;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo e($co->name); ?></div>
                                        <div class="top-bar-wrap mt-1">
                                            <div class="top-bar-fill" style="width:<?php echo e($barPct); ?>%;background:<?php echo e($barCol); ?>;"></div>
                                        </div>
                                    </div>
                                    <div style="font-size:.82rem;font-weight:700;color:<?php echo e($barCol); ?>;min-width:36px;text-align:right;"><?php echo e($avgSc); ?></div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        
        <?php if($recentReports->count() > 0): ?>
            <div class="dash-panel mb-4">
                <div class="dash-panel-hdr dash-panel-hdr-gray">
                    <h5>📋 Laporan Terbaru</h5>
                    <p>Daftar laporan KPI HSSE terkini</p>
                </div>
                <div class="dash-panel-body p-0">

                    
                    <div class="d-flex align-items-center justify-content-between px-3 pt-3 pb-2 flex-wrap gap-2">
                        <div id="tbl-total-info" style="font-size:.78rem;color:#6b7280;"></div>
                        <input type="text" id="tbl-search"
                               class="form-control form-control-sm"
                               placeholder="🔍 Cari perusahaan / periode..."
                               style="max-width:220px;font-size:.8rem;">
                    </div>

                    <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 recent-tbl">
                        <thead class="table-light">
                            <tr>
                                <th style="font-size:.78rem;padding:.6rem .75rem;width:36px;">#</th>
                                <?php if($isHsse): ?><th style="font-size:.78rem;padding:.6rem .75rem;">Perusahaan</th><?php endif; ?>
                                <th style="font-size:.78rem;padding:.6rem .75rem;">Periode</th>
                                <th style="font-size:.78rem;padding:.6rem .75rem;">Status</th>
                                <th style="font-size:.78rem;padding:.6rem .75rem;text-align:center;">Score</th>
                                <th style="font-size:.78rem;padding:.6rem .75rem;">Update</th>
                                <th style="font-size:.78rem;padding:.6rem .75rem;"></th>
                            </tr>
                        </thead>
                        <tbody id="tbl-body">
                        <?php $__currentLoopData = $recentReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $sc = $r->total_score;
                                $cl = $sc ? ($sc>=90?'excellent':($sc>=75?'good':($sc>=60?'fair':'poor'))) : 'none';
                            ?>
                            <tr class="tbl-row"
                                data-search="<?php echo e(strtolower(($r->company->name ?? '') . ' ' . ($r->kpiPeriod->label ?? '') . ' ' . $r->status)); ?>">
                                <td style="color:#9ca3af;font-size:.75rem;vertical-align:middle;"><?php echo e($idx+1); ?></td>
                                <?php if($isHsse): ?>
                                    <td style="vertical-align:middle;"><strong style="font-size:.82rem;"><?php echo e($r->company->name ?? '-'); ?></strong></td>
                                <?php endif; ?>
                                <td style="font-size:.82rem;vertical-align:middle;"><?php echo e($r->kpiPeriod->label ?? '-'); ?></td>
                                <td style="vertical-align:middle;">
                                    <span class="badge-kpi-status badge-status-<?php echo e($r->status); ?>"><?php echo e(ucfirst($r->status)); ?></span>
                                </td>
                                <td style="text-align:center;vertical-align:middle;">
                                    <?php if($sc): ?>
                                        <span class="score-pill <?php echo e($cl); ?>" style="width:38px;height:38px;font-size:.72rem;"><?php echo e(number_format($sc,0)); ?></span>
                                    <?php else: ?>
                                        <span class="score-pill none" style="width:38px;height:38px;font-size:.72rem;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:.76rem;color:#9ca3af;vertical-align:middle;"><?php echo e($r->updated_at->diffForHumans()); ?></td>
                                <td style="vertical-align:middle;">
                                    <a href="<?php echo e(route('kpi-hsse.show', $r)); ?>"
                                       class="btn btn-sm btn-outline-secondary"
                                       style="font-size:.72rem;padding:2px 9px;">Lihat</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                    </div>

                    
                    <div class="tbl-footer">
                        <div class="pag-info" id="pag-info"></div>
                        <div class="pag-btns" id="pag-btns"></div>
                    </div>

                </div>
            </div>
        <?php endif; ?>

    </div>
    </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
/* ── Shared ───────────────────────────────────────────────────────────── */
<?php
    $chartCompanies = $chartData->pluck('company_name')->unique()->values();
    $palette = ['#ef4444','#f97316','#3b82f6','#8b5cf6','#14b8a6','#f59e0b','#10b981','#6366f1'];
?>
const chartCompanies = <?php echo json_encode($chartCompanies, 15, 512) ?>;
const rawData        = <?php echo json_encode($chartData, 15, 512) ?>;
const palette        = <?php echo json_encode($palette, 15, 512) ?>;
const monthMap       = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];

/* ── Bar chart (dynamic height based on company count) ───────────────── */
const periodMap = {};
rawData.forEach(row => {
    const lbl = (monthMap[row.mo]||row.mo)+' '+row.yr;
    if (!periodMap[lbl]) periodMap[lbl] = {};
    periodMap[lbl][row.company_name] = parseFloat(row.total_score)||0;
});
const datasets = Object.entries(periodMap).map(([period,vals],idx) => ({
    label: period,
    data: chartCompanies.map(c => vals[c]||0),
    backgroundColor: palette[idx%palette.length]+'cc',
    borderColor: palette[idx%palette.length],
    borderWidth: 1.5, borderRadius: 4,
}));

const barCanvas = document.getElementById('kpi-bar-chart');
const barCtx    = barCanvas?.getContext('2d');
if (barCtx) {
    if (chartCompanies.length > 0 && datasets.length > 0) {
        // 44px per row keeps bars readable, min 180px
        const dynH = Math.max(180, chartCompanies.length * 44 + 60);
        barCanvas.style.height = dynH + 'px';
        barCanvas.height = dynH;

        new Chart(barCtx, {
            type: 'bar',
            data: { labels: chartCompanies, datasets },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position:'bottom', labels:{ font:{size:11}, boxWidth:12 } },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.raw.toFixed(2)}` } }
                },
                scales: {
                    x: { min:0, max:100, title:{display:true,text:'Score (0–100)',font:{size:11}}, grid:{color:'#e5e7eb'} },
                    y: { grid:{display:false}, ticks:{font:{size:11}} }
                }
            }
        });
    } else {
        barCanvas.parentElement.innerHTML =
            '<div style="text-align:center;color:#9ca3af;padding:48px;font-size:.85rem;">Belum ada data validated.<br>Gunakan filter atau tunggu laporan divalidasi.</div>';
    }
}

/* ── Trend chart ─────────────────────────────────────────────────────── */
const trendData = <?php echo json_encode($trend, 15, 512) ?>;
const trendCtx  = document.getElementById('trend-chart')?.getContext('2d');
if (trendCtx && trendData.length > 0) {
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendData.map(t => (monthMap[t.mo]||t.mo)+' '+t.yr),
            datasets: [
                { label:'Total Score', data: trendData.map(t => parseFloat(t.avg_score||0).toFixed(2)),
                  borderColor:'#2563eb', backgroundColor:'#2563eb22',
                  borderWidth:2.5, pointRadius:4, tension:.35, fill:true },
                { label:'Lagging', data: trendData.map(t => parseFloat(t.avg_lagging||0).toFixed(2)),
                  borderColor:'#f59e0b', backgroundColor:'transparent',
                  borderWidth:1.5, borderDash:[4,3], pointRadius:3, tension:.35 },
                { label:'Leading', data: trendData.map(t => parseFloat(t.avg_leading||0).toFixed(2)),
                  borderColor:'#14b8a6', backgroundColor:'transparent',
                  borderWidth:1.5, borderDash:[4,3], pointRadius:3, tension:.35 },
            ]
        },
        options: {
            responsive:true,
            plugins:{ legend:{ position:'bottom', labels:{ font:{size:11} } } },
            scales:{
                y:{ min:0, max:100, grid:{color:'#e5e7eb'} },
                x:{ grid:{display:false} }
            }
        }
    });
} else if (trendCtx) {
    trendCtx.canvas.parentElement.innerHTML =
        '<div style="text-align:center;color:#9ca3af;padding:40px;font-size:.85rem;">Belum ada trend data.</div>';
}

/* ── Table pagination + search ───────────────────────────────────────── */
(function () {
    const PER_PAGE = 10;
    let currentPage = 1;

    const allRows   = Array.from(document.querySelectorAll('#tbl-body .tbl-row'));
    const searchEl  = document.getElementById('tbl-search');
    const pagBtns   = document.getElementById('pag-btns');
    const pagInfo   = document.getElementById('pag-info');
    const totalInfo = document.getElementById('tbl-total-info');

    if (totalInfo) totalInfo.textContent = `Total: ${allRows.length} laporan`;

    function getFiltered() {
        const q = (searchEl?.value||'').trim().toLowerCase();
        return q ? allRows.filter(r => r.dataset.search.includes(q)) : allRows;
    }

    function render() {
        const rows  = getFiltered();
        const total = rows.length;
        const pages = Math.max(1, Math.ceil(total / PER_PAGE));
        if (currentPage > pages) currentPage = pages;

        const start = (currentPage - 1) * PER_PAGE;
        const end   = Math.min(start + PER_PAGE, total);

        allRows.forEach(r => r.style.display = 'none');
        rows.slice(start, end).forEach(r => r.style.display = '');

        if (pagInfo) {
            pagInfo.textContent = total === 0
                ? 'Tidak ada data yang cocok'
                : `Menampilkan ${start+1}–${end} dari ${total} laporan`;
        }

        if (!pagBtns) return;
        pagBtns.innerHTML = '';

        // Prev button
        const prev = btn('‹', () => { currentPage--; render(); });
        prev.disabled = currentPage === 1;
        pagBtns.appendChild(prev);

        // Page numbers with ellipsis
        const delta = 2;
        let lo = Math.max(1, currentPage - delta);
        let hi = Math.min(pages, currentPage + delta);
        if (currentPage - delta < 1) hi = Math.min(pages, hi + (delta - currentPage + 1));
        if (currentPage + delta > pages) lo = Math.max(1, lo - (currentPage + delta - pages));

        if (lo > 1) { pagBtns.appendChild(numBtn(1)); if (lo > 2) pagBtns.appendChild(dots()); }
        for (let p = lo; p <= hi; p++) pagBtns.appendChild(numBtn(p));
        if (hi < pages) { if (hi < pages-1) pagBtns.appendChild(dots()); pagBtns.appendChild(numBtn(pages)); }

        // Next button
        const next = btn('›', () => { currentPage++; render(); });
        next.disabled = currentPage === pages;
        pagBtns.appendChild(next);
    }

    function btn(label, onclick) {
        const b = document.createElement('button');
        b.className = 'pag-btn'; b.textContent = label; b.onclick = onclick;
        return b;
    }
    function numBtn(p) {
        const b = btn(p, () => { currentPage = p; render(); });
        if (p === currentPage) b.classList.add('active');
        return b;
    }
    function dots() {
        const s = document.createElement('span');
        s.textContent = '…'; s.style.cssText = 'padding:4px 4px;font-size:.75rem;color:#9ca3af;';
        return s;
    }

    if (searchEl) searchEl.addEventListener('input', () => { currentPage = 1; render(); });
    render();
})();
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/kpi-hsse/dashboard.blade.php ENDPATH**/ ?>