
<div class="d-flex align-items-center justify-content-end gap-1">

    
    <a href="<?php echo e(route('users.show', $user)); ?>"
       class="btn btn-sm btn-outline-primary"
       data-bs-toggle="tooltip"
       title="View Details">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
        </svg>
    </a>

    
    <a href="<?php echo e(route('users.edit', $user)); ?>"
       class="btn btn-sm btn-outline-info"
       data-bs-toggle="tooltip"
       title="Edit User">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2">
            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
        </svg>
    </a>

    
    <button type="button"
            class="btn btn-sm btn-outline-danger delete-record"
            data-url="<?php echo e(route('users.destroy', $user)); ?>"
            data-bs-toggle="tooltip"
            title="Delete User">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2">
            <polyline points="3 6 5 6 21 6"></polyline>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
        </svg>
    </button>

</div>

<?php if (! $__env->hasRenderedOnce('4b52a9cc-d8da-4c7d-ad17-88346a528b85')): $__env->markAsRenderedOnce('4b52a9cc-d8da-4c7d-ad17-88346a528b85'); ?>
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
<?php /**PATH /home/kaptensa/salman/resources/views/master/users/partials/actions.blade.php ENDPATH**/ ?>