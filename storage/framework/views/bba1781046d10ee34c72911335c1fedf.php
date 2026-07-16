
<div class="footer-wrapper <?php echo e($class ?? ''); ?>">
    <div class="footer-section f-section-1">
        <p class="">Copyright © <span class="dynamic-year"><?php echo e(date('Y')); ?></span>
            <a target="_blank" href="/">PHM</a>, PO/SUP.
        </p>
    </div>
    <div class="footer-section f-section-2">
        <p class="">Coded with <?php if (isset($component)) { $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feather-icon','data' => ['name' => 'heart']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feather-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'heart']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $attributes = $__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__attributesOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8)): ?>
<?php $component = $__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8; ?>
<?php unset($__componentOriginal78e0e8e6edeecf814e3a31b0ebe65ac8); ?>
<?php endif; ?></p>
    </div>
</div>
<?php /**PATH /home/kaptensa/salman/resources/views/components/footer.blade.php ENDPATH**/ ?>