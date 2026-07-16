
<?php $__env->startSection('title', 'Crew Assessment'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/src/table/datatable/datatables.css')); ?>">
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/css/light/table/datatable/dt-global_style.css')); ?>">
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/css/dark/table/datatable/dt-global_style.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.css')); ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
.filter-card { background:var(--card-bg);border-radius:10px;padding:1.25rem 1.5rem;margin-bottom:1.5rem;border:1px solid var(--card-border-color); }
.filter-card .form-label { font-weight:700;font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;color:var(--text-color);margin-bottom:.35rem; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="layout-px-spacing">
<div class="middle-content container-xxl p-0">

    
    <div class="row layout-top-spacing mb-4 align-items-center">
        <div class="col-md-6">
            <h3 class="m-0 fw-bold">Crew Assessment</h3>
            <p class="text-muted mb-0 small">Database assessment & sertifikasi kru kapal.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end gap-2 flex-wrap">
            <a href="<?php echo e(route('crew-assessment.dashboard')); ?>" class="btn btn-outline-info btn-sm">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard
            </a>
            <button type="button" class="btn btn-success btn-sm" id="btnExport">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </button>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage crew assessment')): ?>
            <a href="<?php echo e(route('crew-assessment.create')); ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah
            </a>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="filter-card">
        <div class="row g-3">
            <div class="col-lg-2 col-md-4 col-6">
                <label class="form-label">Start Date</label>
                <input type="text" class="form-control form-control-sm flatpickr" id="start_date" placeholder="YYYY-MM-DD">
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <label class="form-label">End Date</label>
                <input type="text" class="form-control form-control-sm flatpickr" id="end_date" placeholder="YYYY-MM-DD">
            </div>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage crew assessment')): ?>
            <div class="col-lg-2 col-md-4 col-6">
                <label class="form-label">Perusahaan</label>
                <select class="form-select form-select-sm" id="filter_company">
                    <option value="">Semua</option>
                    <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-lg-2 col-md-4 col-6">
                <label class="form-label">Kapal</label>
                <select class="form-select form-select-sm" id="filter_vessel">
                    <option value="">Semua</option>
                    <?php $__currentLoopData = $vessels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($v->id); ?>"><?php echo e($v->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <label class="form-label">MEV Type</label>
                <select class="form-select form-select-sm" id="filter_mev">
                    <option value="">Semua</option>
                    <?php $__currentLoopData = \App\Models\CrewAssessment::MEV_TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($k); ?>"><?php echo e($v); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <label class="form-label">Hasil</label>
                <select class="form-select form-select-sm" id="filter_result">
                    <option value="">Semua</option>
                    <?php $__currentLoopData = \App\Models\CrewAssessment::RESULTS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($k); ?>"><?php echo e($v); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <label class="form-label">Lokasi</label>
                <select class="form-select form-select-sm" id="filter_location">
                    <option value="">Semua</option>
                    <?php $__currentLoopData = \App\Models\CrewAssessment::LOCATIONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($k); ?>"><?php echo e($k); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <label class="form-label">Jabatan Diusulkan</label>
                <select class="form-select form-select-sm" id="filter_position">
                    <option value="">Semua</option>
                    <?php $__currentLoopData = \App\Models\CrewAssessment::POSITIONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($k); ?>"><?php echo e($v); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <label class="form-label">Jabatan Tipe</label>
                <select class="form-select form-select-sm" id="filter_position_type">
                    <option value="">Semua</option>
                    <?php $__currentLoopData = \App\Models\CrewAssessment::POSITION_TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($k); ?>"><?php echo e($v); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
        <div class="mt-3">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="resetFilters">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
            </button>
        </div>
    </div>

    
    <div class="statbox widget box box-shadow">
        <div class="widget-content widget-content-area">
            <div class="table-responsive">
                <table id="assessmentTable" class="table dt-table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No. Sertifikat</th>
                            <th>Nama</th>
                            <th>Tgl Lahir</th>
                            <th>Jabatan Diusulkan</th>
                            <th>Promote/Refresh</th>
                            <th>Nama Kapal</th>
                            <th>Exp. Pertamina</th>
                            <th>Exp. Master</th>
                            <th>Exp. Diluar</th>
                            <th>MEV</th>
                            <th>Tgl Assessment</th>
                            <th>Lokasi</th>
                            <th>MAR</th>
                            <th>HSE</th>
                            <th>FMC</th>
                            <th class="text-center">Hasil</th>
                            <th>Perusahaan</th>
                            <th class="text-center">Lamp.</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>



</div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('plugins/src/table/datatable/datatables.js')); ?>"></script>
<script src="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.min.js')); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
(function ($) {
    'use strict';
    flatpickr('.flatpickr', { dateFormat:'Y-m-d', allowInput:true });
    const Toast = Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:3000, timerProgressBar:true });

    <?php if(session('success')): ?>
        Toast.fire({ icon: 'success', title: "<?php echo e(session('success')); ?>" });
    <?php endif; ?>
    <?php if(session('error')): ?>
        Toast.fire({ icon: 'error', title: "<?php echo e(session('error')); ?>" });
    <?php endif; ?>

    const table = $('#assessmentTable').DataTable({
        processing:true, serverSide:true,
        ajax: {
            url:'<?php echo e(route("crew-assessment.index.data")); ?>',
            data: d => {
                d.start_date         = $('#start_date').val();
                d.end_date           = $('#end_date').val();
                d.company_id         = $('#filter_company').val()       || '';
                d.vessel_id          = $('#filter_vessel').val()         || '';
                d.mev_type           = $('#filter_mev').val()            || '';
                d.result             = $('#filter_result').val()          || '';
                d.assessment_location= $('#filter_location').val()       || '';
                d.position_proposed  = $('#filter_position').val()       || '';
                d.position_type      = $('#filter_position_type').val()  || '';
            }
        },
        columns: [
            { data:'DT_RowIndex',       name:'DT_RowIndex',       orderable:false, searchable:false, width:'40px' },
            { data:'no_sertifikat',     name:'certificate_number' },
            { data:'nama',              name:'crewMember.name' },
            { data:'tgl_lahir',         name:'crew_birth_date', searchable:false, orderable:false },
            { data:'jabatan_diusulkan', name:'position_proposed' },
            { data:'jabatan_tipe',      name:'position_type' },
            { data:'kapal',             name:'vessel.name', orderable:false },
            { data:'exp_pertamina',     name:'experience_pertamina', orderable:false },
            { data:'exp_master',        name:'experience_master',    orderable:false },
            { data:'exp_diluar',        name:'experience_outside',   orderable:false },
            { data:'mev',               name:'mev_type' },
            { data:'tgl_assessment',    name:'assessment_date' },
            { data:'lokasi',            name:'assessment_location' },
            { data:'assessor_mar',      name:'assessor_mar', orderable:false },
            { data:'assessor_hse',      name:'assessor_hse', orderable:false },
            { data:'assessor_fmc',      name:'assessor_fmc', orderable:false },
            { data:'hasil_badge',       name:'result',       className:'text-center', orderable:false },
            { data:'perusahaan',        name:'company.name', orderable:false },
            { data:'lamp_count',        name:'lamp_count',   className:'text-center', orderable:false,
              render: d => d ? '<span class="badge bg-info">'+d+'</span>' : '<span class="text-muted">—</span>' },
            { data:'action',            name:'action',       orderable:false, searchable:false, className:'text-center' }
        ],
        dom:'<"dt--top-section"<"row"<"col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center"l><"col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3"f>>><"table-responsive"tr><"dt--bottom-section d-sm-flex justify-content-sm-between text-center"<"dt--pages-count mb-sm-0 mb-3"i><"dt--pagination"p>>',
        oLanguage: {
            oPaginate: {
                sPrevious:'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>',
                sNext:    '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>'
            },
            sInfo:'Showing _START_–_END_ of _TOTAL_', sSearch:'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
            sSearchPlaceholder:'Search...', sLengthMenu:'Results: _MENU_'
        },
        stripeClasses:[], lengthMenu:[10,25,50,100], pageLength:25, order:[[11,'desc']]
    });

    $('#start_date,#end_date,#filter_company,#filter_vessel,#filter_mev,#filter_result,#filter_location,#filter_position,#filter_position_type').on('change', () => table.draw());
    $('#resetFilters').on('click', () => {
        $('#start_date,#end_date').val('');
        $('#filter_company,#filter_vessel,#filter_mev,#filter_result,#filter_location,#filter_position,#filter_position_type').val('');
        table.draw();
    });

    $('#btnExport').on('click', function () {
        const s = $('#start_date').val(), e = $('#end_date').val();
        if (!s || !e) { Swal.fire({ icon:'warning', title:'Tanggal wajib diisi', text:'Pilih Start Date dan End Date.' }); return; }
        const p = new URLSearchParams({
            start_date:s, end_date:e,
            company_id:          $('#filter_company').val()      || '',
            vessel_id:           $('#filter_vessel').val()        || '',
            mev_type:            $('#filter_mev').val()           || '',
            result:              $('#filter_result').val()         || '',
            assessment_location: $('#filter_location').val()      || '',
        });
        Toast.fire({ icon:'info', title:'Memproses export...' });
        window.location.href = '<?php echo e(route("crew-assessment.export-excel")); ?>?' + p.toString();
    });

    $(document).on('click', '.delete-record', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({
            title:'Hapus Assessment?', html:'<strong>'+name+'</strong> akan dihapus permanen.', icon:'warning',
            showCancelButton:true, confirmButtonColor:'#e7515a', cancelButtonColor:'#3b3f5c',
            confirmButtonText:'Ya, hapus!', cancelButtonText:'Batal'
        }).then(r => {
            if (r.isConfirmed) $.ajax({
                url, type:'DELETE', data:{_token:'<?php echo e(csrf_token()); ?>'},
                success: r => { if(r.success){table.draw();Toast.fire({icon:'success',title:r.message});}else Toast.fire({icon:'error',title:r.message}); },
                error: () => Toast.fire({icon:'error',title:'Gagal menghapus.'})
            });
        });
    });
}(jQuery));
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/features/crew-assessment/index.blade.php ENDPATH**/ ?>