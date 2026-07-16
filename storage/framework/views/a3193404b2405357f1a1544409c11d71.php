<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Kegiatan - <?php echo e($campaignSalman->tema); ?></title>
    <style>
        @page {
            margin: 0px;
            size: A4;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333333;
            line-height: 1.5;
            background-color: transparent;
            padding-top: 130px;
            padding-bottom: 80px;
            padding-left: 50px;
            padding-right: 50px;
        }

        /* Background Template */
        .page-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -10;
            background-image: url("<?php echo e((isset($campaignSalman) && $campaignSalman->coverTemplate) ? asset('storage/' . $campaignSalman->coverTemplate->page_image_path) : ''); ?>");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        /* Cover Page */
        .cover-page {
            margin-top: -130px;
            margin-bottom: -80px;
            margin-left: -50px;
            margin-right: -50px;
            height: 297mm;
            width: 210mm;
            position: relative;
            z-index: 10;
            page-break-after: always;
            background-image: url("<?php echo e((isset($campaignSalman) && $campaignSalman->coverTemplate) ? asset('storage/' . $campaignSalman->coverTemplate->cover_image_path) : ''); ?>");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        /* Cover Overlay untuk Template dengan Background */
        .cover-overlay {
            position: absolute;
            bottom: 50px;
            left: 50px;
            right: 50px;
            background: rgba(30, 58, 138, 0.9);
            padding: 30px;
            border-radius: 8px;
            color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        .cover-overlay-title {
            font-size: 28px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 15px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
            padding-bottom: 10px;
        }

        .cover-overlay-company {
            font-size: 20px;
            font-weight: 600;
            color: #fbbf24;
            margin-bottom: 10px;
        }

        .cover-overlay-entitas {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .cover-overlay-date {
            font-size: 14px;
            opacity: 0.9;
        }

        /* Fallback Cover dengan Nama Perusahaan */
        .cover-fallback {
            display: table;
            width: 100%;
            height: 100%;
            background-color: #1e3a8a;
            color: #ffffff;
            text-align: center;
        }

        .cover-content-fallback {
            display: table-cell;
            vertical-align: middle;
            padding: 0 40px;
        }

        .cover-title {
            font-size: 32px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
            border-bottom: 2px solid rgba(255,255,255,0.3);
            display: inline-block;
            padding-bottom: 15px;
        }

        .cover-company {
            font-size: 24px;
            font-weight: 600;
            margin-top: 20px;
            margin-bottom: 15px;
            color: #fbbf24;
        }

        /* Section Title */
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 30px;
            margin-bottom: 15px;
            padding-left: 10px;
            border-left: 5px solid #1e3a8a;
            line-height: 1.2;
            page-break-after: avoid;
        }

        .mt-0 { margin-top: 0 !important; }

        /* Details Table */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 14px;
            border: 1px solid #d1d5db;
        }

        .details-table td {
            padding: 12px 15px;
            border: 1px solid #d1d5db;
            vertical-align: top;
        }

        .details-label {
            width: 25%;
            background-color: #f3f4f6;
            font-weight: 700;
            color: #374151;
        }

        .details-value {
            width: 75%;
            background-color: #ffffff;
            color: #111827;
        }

        /* Content Text */
        .content-text {
            font-size: 14px;
            text-align: justify;
            line-height: 1.6;
            color: #374151;
            margin-bottom: 20px;
        }

        /* Photo Grid */
        .photo-grid {
            width: 100%;
            margin-top: 10px;
        }

        .photo-item {
            width: 48%;
            float: left;
            margin-bottom: 20px;
            background: #fff;
            padding: 5px;
            border: 1px solid #e5e7eb;
            box-sizing: border-box;
            page-break-inside: avoid;
        }

        .photo-item:nth-child(odd) {
            margin-right: 4%;
        }

        .photo-item img {
            width: 100%;
            height: 210px;
            object-fit: cover;
            display: block;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* Daftar Hadir */
        .hadir-item {
            text-align: center;
            margin-bottom: 25px;
            border: 1px solid #e5e7eb;
            padding: 5px;
            background: #fff;
            page-break-inside: avoid;
        }

        .hadir-item img {
            width: 100%;
            height: auto;
            max-height: 750px;
            object-fit: contain;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <!-- Background -->
    <div class="page-background"></div>

    <!-- Cover Page -->
    <div class="cover-page">
        <?php if(!isset($campaignSalman->coverTemplate) || !$campaignSalman->coverTemplate): ?>
            <!-- Fallback Cover tanpa Template -->
            <div class="cover-fallback">
                <div class="cover-content-fallback">
                    <div class="cover-title"><?php echo e($campaignSalman->tema); ?></div>

                    <!-- NAMA PERUSAHAAN -->
                    <?php if($campaignSalman->company): ?>
                        <div class="cover-company"><?php echo e($campaignSalman->company->name); ?></div>
                    <?php endif; ?>

                    <p style="font-size: 20px; margin: 0;"><?php echo e($campaignSalman->entitas); ?></p>
                    <br>
                    <p style="font-size: 16px; opacity: 0.8;">
                        <?php echo e($campaignSalman->tanggal->isoFormat('D MMMM Y')); ?>

                    </p>
                </div>
            </div>
        <?php else: ?>
            <!-- Cover dengan Template Background - Overlay di bagian bawah -->
            <div class="cover-overlay">
                <div class="cover-overlay-title"><?php echo e($campaignSalman->tema); ?></div>

                <!-- NAMA PERUSAHAAN -->
                <?php if($campaignSalman->company): ?>
                    <div class="cover-overlay-company"><?php echo e($campaignSalman->company->name); ?></div>
                <?php endif; ?>

                <div class="cover-overlay-entitas"><?php echo e($campaignSalman->entitas); ?></div>
                <div class="cover-overlay-date"><?php echo e($campaignSalman->tanggal->isoFormat('dddd, D MMMM Y')); ?></div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Detail Kegiatan -->
    <div class="section-title mt-0">Detail Kegiatan</div>
    <table class="details-table">
        <!-- TAMBAHAN: Nama Perusahaan di tabel -->
        <?php if($campaignSalman->company): ?>
        <tr>
            <td class="details-label">Perusahaan</td>
            <td class="details-value"><?php echo e($campaignSalman->company->name); ?></td>
        </tr>
        <?php endif; ?>

        <tr>
            <td class="details-label">Tema Kegiatan</td>
            <td class="details-value"><?php echo e($campaignSalman->tema); ?></td>
        </tr>
        <tr>
            <td class="details-label">Tanggal Pelaksanaan</td>
            <td class="details-value"><?php echo e($campaignSalman->tanggal->isoFormat('dddd, D MMMM Y')); ?></td>
        </tr>
        <tr>
            <td class="details-label">Lokasi</td>
            <td class="details-value"><?php echo e($campaignSalman->lokasi); ?></td>
        </tr>
        <tr>
            <td class="details-label">Jumlah Peserta</td>
            <td class="details-value"><?php echo e($campaignSalman->jumlah_peserta); ?> Orang</td>
        </tr>
        <tr>
            <td class="details-label">Pemateri</td>
            <td class="details-value"><?php echo e($campaignSalman->pemateri); ?></td>
        </tr>
        <tr>
            <td class="details-label">Entitas</td>
            <td class="details-value"><?php echo e($campaignSalman->entitas); ?></td>
        </tr>
    </table>

    <!-- Ringkasan -->
    <div class="section-title">Ringkasan Kegiatan</div>
    <div class="content-text">
        <?php echo nl2br(e($campaignSalman->ringkasan)); ?>

    </div>

    <!-- Dokumentasi -->
    <?php if(isset($campaignSalman->dokumentasi) && count($campaignSalman->dokumentasi) > 0): ?>
        <div class="section-title">Dokumentasi Kegiatan</div>
        <div class="photo-grid clearfix">
            <?php $__currentLoopData = $campaignSalman->dokumentasi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $foto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="photo-item">
                    <img src="<?php echo e(asset('storage/' . $foto)); ?>" alt="Dokumentasi">
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <!-- Daftar Hadir -->
    <?php if(isset($campaignSalman->daftar_hadir) && count($campaignSalman->daftar_hadir) > 0): ?>
        <div class="page-break"></div>
        <div class="section-title">Daftar Hadir</div>
        <div>
            <?php $__currentLoopData = $campaignSalman->daftar_hadir; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hadir): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="hadir-item">
                    <img src="<?php echo e(asset('storage/' . $hadir)); ?>" alt="Absensi">
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

</body>
</html>
<?php /**PATH /home/kaptensa/salman/resources/views/features/campaign-salman/pdf.blade.php ENDPATH**/ ?>