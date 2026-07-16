<?php
    $item = $item ?? null;
    if (!$item) return;

    $isOverdue = $item->target_date->isPast() && !in_array($item->status, ['Completed', 'Cannot Do']);
    $dueDateStatus = $isOverdue ? 'is-overdue' : '';

    $categoryName = optional($item->actionCategory)->name ?? 'Uncategorized';
    $priority = 'Rendah';
    $priorityColor = 'green';

    if (Str::contains($categoryName, ['1', 'Tinggi', 'High'])) {
        $priority = 'Tinggi';
        $priorityColor = 'red';
    } elseif (Str::contains($categoryName, ['2', 'Sedang', 'Medium'])) {
        $priority = 'Sedang';
        $priorityColor = 'orange';
    }

    $searchTerm = Str::lower(
        $item->description . ' ' .
        $item->cermatReport->report_number . ' ' .
        optional($item->cermatReport->area)->name
    );

    // Calculate days until/past due date
    $daysUntilDue = now()->diffInDays($item->target_date, false);
    $dueDateText = '';

    if ($isOverdue) {
        $daysPast = abs($daysUntilDue);
        $dueDateText = $daysPast == 0 ? 'Due today' : $daysPast . ' days overdue';
    } else {
        $dueDateText = $daysUntilDue == 0 ? 'Due today' :
                      ($daysUntilDue == 1 ? 'Due tomorrow' :
                      'Due in ' . $daysUntilDue . ' days');
    }
?>

<div class="kanban-card priority-<?php echo e($priority); ?>"
     data-id="<?php echo e($item->id); ?>"
     data-search-term="<?php echo e($searchTerm); ?>"
     data-priority="<?php echo e($priority); ?>"
     data-due-date="<?php echo e($item->target_date->toDateString()); ?>">

    <div class="card-header-section">
        <span class="priority-badge bg-light-<?php echo e(strtolower($priorityColor)); ?>">
            <?php echo e($priority); ?> Priority
        </span>

        <?php if($isOverdue): ?>
            <span class="overdue-badge">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Overdue
            </span>
        <?php endif; ?>
    </div>

    <p class="card-title"><?php echo e($item->description); ?></p>

    <div class="card-meta">
        <span class="meta-badge">
            <i class="bi bi-file-earmark-text"></i>
            <a href="<?php echo e(route('cermat.reports.show', $item->cermat_report_id)); ?>"
               target="_blank"
               onclick="event.stopPropagation()"
               title="View Report">
                #<?php echo e($item->cermatReport->report_number); ?>

            </a>
        </span>

        <span class="meta-badge meta-area">
            <i class="bi bi-geo-alt"></i>
            <span title="<?php echo e(optional($item->cermatReport->area)->name ?? 'N/A'); ?>">
                <?php echo e(Str::limit(optional($item->cermatReport->area)->name ?? 'N/A', 20)); ?>

            </span>
        </span>

        <?php if($item->actionCategory): ?>
            <span class="meta-badge meta-category">
                <i class="bi bi-tag"></i>
                <?php echo e(Str::limit($item->actionCategory->name, 25)); ?>

            </span>
        <?php endif; ?>
    </div>

    <div class="card-footer">
        <div class="date-meta <?php echo e($dueDateStatus); ?>" title="<?php echo e($item->target_date->isoFormat('dddd, DD MMMM YYYY')); ?>">
            <i class="bi <?php echo e($isOverdue ? 'bi-calendar-x' : 'bi-calendar3'); ?>"></i>
            <div class="date-info">
                <span class="date-value"><?php echo e($item->target_date->isoFormat('DD MMM YYYY')); ?></span>
                <span class="date-relative"><?php echo e($dueDateText); ?></span>
            </div>
        </div>

        <div class="card-indicators">
            <?php if($item->completion_notes): ?>
                <div class="indicator indicator-notes" title="Has completion notes">
                    <i class="bi bi-chat-left-text-fill"></i>
                </div>
            <?php endif; ?>

            <?php if($item->photos->isNotEmpty()): ?>
                <div class="indicator indicator-photos" title="<?php echo e($item->photos->count()); ?> photo(s)">
                    <i class="bi bi-images"></i>
                    <span class="indicator-count"><?php echo e($item->photos->count()); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="card-hover-overlay">
        <i class="bi bi-arrow-right-circle"></i>
        <span>Click to view details</span>
    </div>
</div>

<style>
    /* Card Header Section */
    .card-header-section {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .overdue-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        background: #fee2e2;
        color: #991b1b;
        animation: pulse-overdue 2s infinite;
    }

    @keyframes pulse-overdue {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
        }
        50% {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0);
        }
    }

    /* Enhanced Meta Badges */
    .meta-badge.meta-area i {
        color: #10b981;
    }

    .meta-badge.meta-category i {
        color: #8b5cf6;
    }

    /* Enhanced Date Display */
    .date-info {
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
    }

    .date-value {
        font-weight: 600;
        font-size: 0.8rem;
    }

    .date-relative {
        font-size: 0.7rem;
        opacity: 0.8;
    }

    .date-meta.is-overdue .date-relative {
        font-weight: 700;
    }

    /* Enhanced Indicators */
    .indicator-notes {
        color: #8b5cf6;
    }

    .indicator-photos {
        color: #0ea5e9;
    }

    .indicator-count {
        font-weight: 700;
        font-size: 0.75rem;
        background: currentColor;
        color: white;
        padding: 0.1rem 0.4rem;
        border-radius: 10px;
        min-width: 18px;
        text-align: center;
    }

    /* Hover Overlay */
    .card-hover-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.95) 0%, rgba(124, 58, 237, 0.95) 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        color: white;
        opacity: 0;
        transition: all 0.3s ease;
        border-radius: 14px;
        pointer-events: none;
    }

    .card-hover-overlay i {
        font-size: 2rem;
    }

    .card-hover-overlay span {
        font-size: 0.9rem;
        font-weight: 600;
    }

    .kanban-card:hover .card-hover-overlay {
        opacity: 1;
    }

    /* Smooth transitions for all card elements */
    .kanban-card * {
        transition: all 0.2s ease;
    }
</style>
<?php /**PATH /home/kaptensa/salman/resources/views/user/partials/kanban_card.blade.php ENDPATH**/ ?>