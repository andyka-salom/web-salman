
<div class="header-container container-xxl">
    <header class="header navbar navbar-expand-sm expand-header">

        <a href="javascript:void(0);" class="sidebarCollapse">
            <?php if (isset($component)) { $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feather-icon','data' => ['name' => 'menu']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feather-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'menu']); ?>
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
        </a>

        <!-- <div class="search-animated toggle-search">
            <?php if (isset($component)) { $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feather-icon','data' => ['name' => 'search']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feather-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search']); ?>
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
            <form class="form-inline search-full form-inline search" role="search">
                <div class="search-bar">
                    <input type="text" class="form-control search-form-control ml-lg-auto" placeholder="Search...">
                    <?php if (isset($component)) { $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feather-icon','data' => ['name' => 'x','class' => 'search-close']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feather-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x','class' => 'search-close']); ?>
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
            </form>
            <span class="badge badge-secondary">Ctrl + /</span>
        </div> -->

        <ul class="navbar-item flex-row ms-lg-auto ms-0">
            <?php echo $__env->make('components.navbar.theme-toggle', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('components.navbar.user-profile-dropdown', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        </ul>
    </header>
</div>
<?php /**PATH /home/kaptensa/salman/resources/views/components/navbar.blade.php ENDPATH**/ ?>