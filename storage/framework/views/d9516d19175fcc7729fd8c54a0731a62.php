<!--  BEGIN SIDEBAR  -->
<div class="sidebar-wrapper sidebar-theme">
    <nav id="sidebar">
        <div class="navbar-nav theme-brand flex-row text-center">
            <div class="nav-logo">
                <div class="nav-item theme-logo">
                    <a href="/">
                        <img src="<?php echo e(asset('logo.png')); ?>">
                    </a>
                </div>
                <div class="nav-item theme-text">
                    <a href="<?php echo e(route('home')); ?>" class="nav-link">
                        <?php echo e(config('app.name', 'EQUATION')); ?>

                    </a>
                </div>
            </div>
            <div class="nav-item sidebar-toggle">
                <div class="btn-toggle sidebarCollapse">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevrons-left">
                        <polyline points="11 17 6 12 11 7"></polyline>
                        <polyline points="18 17 13 12 18 7"></polyline>
                    </svg>
                </div>
            </div>
        </div>

        <?php if(auth()->guard()->check()): ?>
        <div class="profile-info">
            <div class="user-info">
                <div class="profile-img">
                    <img src="<?php echo e(Auth::user()->photo_path ? asset('storage/' . Auth::user()->photo_path) : asset('src/assets/img/profile-30.png')); ?>" alt="avatar">
                </div>
                <div class="profile-content">
                    <h6 class=""><?php echo e(Auth::user()->name); ?></h6>
                    <p class=""><?php echo e(Auth::user()->getRoleNames()->map(fn($role) => ucwords($role))->implode(', ')); ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>


        <div class="shadow-bottom"></div>

        <?php echo $__env->make('components.menu', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </nav>
</div>
<?php /**PATH /home/kaptensa/salman/resources/views/components/sidebar.blade.php ENDPATH**/ ?>