
<div class="d-flex justify-content-center gap-1">

    
    <a href="<?php echo e(route('contacts.show', $c)); ?>"
       class="btn btn-sm btn-outline-info"
       style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;"
       title="Lihat Detail">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
             <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
             <circle cx="12" cy="12" r="3"></circle>
        </svg>
    </a>

    
    <a href="<?php echo e(route('contacts.edit', $c)); ?>"
       class="btn btn-sm btn-outline-primary"
       style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;"
       title="Edit">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
             <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
             <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
        </svg>
    </a>

    
    <button type="button"
            class="btn btn-sm <?php echo e($c->is_active ? 'btn-outline-warning' : 'btn-outline-success'); ?> toggle-status"
            style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;"
            data-url="<?php echo e(route('contacts.toggle-status', $c)); ?>"
            title="<?php echo e($c->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>">
        <?php if($c->is_active): ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                 <circle cx="12" cy="12" r="10"></circle>
                 <line x1="10" y1="15" x2="10" y2="9"></line><line x1="14" y1="15" x2="14" y2="9"></line>
            </svg>
        <?php else: ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                 <circle cx="12" cy="12" r="10"></circle>
                 <polygon points="10 8 16 12 10 16 10 8"></polygon>
            </svg>
        <?php endif; ?>
    </button>

    
    <button type="button"
            class="btn btn-sm btn-outline-danger delete-contact"
            style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;"
            data-url="<?php echo e(route('contacts.destroy', $c)); ?>"
            data-name="<?php echo e(e($c->name)); ?>"
            title="Hapus">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
             <polyline points="3 6 5 6 21 6"></polyline>
             <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
             <path d="M10 11v6"></path><path d="M14 11v6"></path>
             <path d="M9 6V4h6v2"></path>
        </svg>
    </button>

</div>
<?php /**PATH /home/kaptensa/salman/resources/views/master/contacts/partials/actions.blade.php ENDPATH**/ ?>