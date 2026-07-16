
<div class="d-flex align-items-center justify-content-end gap-1">

    
    <a href="<?php echo e(route('roles.edit', $role)); ?>"
       class="btn btn-sm btn-outline-info"
       data-bs-toggle="tooltip"
       title="Edit Role">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2">
            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
        </svg>
    </a>

    
    <?php if($role->name !== 'super-admin'): ?>
        <button type="button"
                class="btn btn-sm btn-outline-danger delete-record"
                data-url="<?php echo e(route('roles.destroy', $role)); ?>"
                data-bs-toggle="tooltip"
                title="Delete Role">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
        </button>
    <?php endif; ?>

</div>

<?php if (! $__env->hasRenderedOnce('585b42c1-c9d2-4422-9a28-7596956ec986')): $__env->markAsRenderedOnce('585b42c1-c9d2-4422-9a28-7596956ec986'); ?>
<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi Tooltip Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH /home/kaptensa/salman/resources/views/master/roles/partials/actions.blade.php ENDPATH**/ ?>