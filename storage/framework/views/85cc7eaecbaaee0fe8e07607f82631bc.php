<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo $__env->yieldContent('title', config('app.name', 'Laravel')); ?></title>

    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('logo.png')); ?>"/>

    <!-- Loader CSS -->
    <link href="<?php echo e(asset('layouts/vertical-light-menu/css/light/loader.css')); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo e(asset('layouts/vertical-light-menu/css/dark/loader.css')); ?>" rel="stylesheet" type="text/css" />
    <script src="<?php echo e(asset('layouts/vertical-light-menu/loader.js')); ?>"></script>

    <!-- Global Styles -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700" rel="stylesheet">
    <link href="<?php echo e(asset('bootstrap/css/bootstrap.min.css')); ?>" rel="stylesheet" type="text/css" />

    <!-- Auth Page Styles -->
    <link href="<?php echo e(asset('layouts/vertical-light-menu/css/light/plugins.css')); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo e(asset('assets/css/light/authentication/auth-boxed.css')); ?>" rel="stylesheet" type="text/css" />

    <link href="<?php echo e(asset('layouts/vertical-light-menu/css/dark/plugins.css')); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo e(asset('assets/css/dark/authentication/auth-boxed.css')); ?>" rel="stylesheet" type="text/css" />

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="form">

    <!-- BEGIN LOADER -->
    <div id="load_screen">
        <div class="loader">
            <div class="loader-content">
                <div class="spinner-grow align-self-center"></div>
            </div>
        </div>
    </div>
    <!--  END LOADER -->

    <?php echo $__env->yieldContent('content'); ?>

    <!-- Scripts -->
    <script src="<?php echo e(asset('bootstrap/js/bootstrap.bundle.min.js')); ?>"></script>

    <?php echo $__env->yieldPushContent('scripts'); ?>

</body>
</html>
<?php /**PATH /home/kaptensa/salman/resources/views/layouts/auth.blade.php ENDPATH**/ ?>