

<?php $__env->startSection('title', 'Group Details - ' . $group->group_name); ?>

<?php $__env->startSection('content'); ?>
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">

            
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <div class="row align-items-center">
                        <div class="col-md-8 mb-3 mb-md-0">
                            <h3 class="mb-2"><?php echo e($group->group_name); ?></h3>
                            <div class="mb-2">
                                <?php if($group->is_active): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inactive</span>
                                <?php endif; ?>
                            </div>
                            <?php if($group->description): ?>
                            <p class="text-muted mb-0"><?php echo e($group->description); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 text-md-end text-center">
                            <a href="<?php echo e(route('groups.members', $group)); ?>" class="btn btn-success mb-2 w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users me-1"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                Manage Members
                            </a>
                            <a href="<?php echo e(route('groups.edit', $group)); ?>" class="btn btn-primary mb-2 w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit me-1"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                Edit
                            </a>
                            <a href="<?php echo e(route('groups.index')); ?>" class="btn btn-outline-secondary w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left me-1"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="col-xl-6 col-lg-6 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <h5 class="mb-4 pb-3 border-bottom">Group Information</h5>

                    <div class="mb-3">
                        <p class="text-muted mb-1 fw-bold" style="font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">Group Name</p>
                        <p class="mb-0"><?php echo e($group->group_name); ?></p>
                    </div>
                    <div class="mb-3">
                        <p class="text-muted mb-1 fw-bold" style="font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">Description</p>
                        <p class="mb-0"><?php echo e($group->description ?? '-'); ?></p>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <p class="text-muted mb-1 fw-bold" style="font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">Created At</p>
                            <p class="mb-0"><?php echo e($group->created_at->format('d M Y, H:i')); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1 fw-bold" style="font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">Last Updated</p>
                            <p class="mb-0"><?php echo e($group->updated_at->format('d M Y, H:i')); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-lg-6 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <h5 class="mb-4 pb-3 border-bottom">Statistics</h5>

                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted fw-bold">Total Members</span>
                        <span class="badge badge-primary"><?php echo e($group->member_count); ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted">Users</span>
                        <span class="badge badge-info"><?php echo e($group->users->count()); ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted">Crew Members</span>
                        <span class="badge badge-warning"><?php echo e($group->crewMembers->count()); ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted">Contacts</span>
                        <span class="badge badge-success"><?php echo e($group->contacts->count()); ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <span class="text-muted">Contracts</span>
                        <span class="badge badge-secondary"><?php echo e($group->contracts->count()); ?></span>
                    </div>
                </div>
            </div>

            
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <h5 class="mb-4 pb-3 border-bottom">Users (<?php echo e($group->users->count()); ?>)</h5>

                    <?php if($group->users->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $group->users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($index + 1); ?></td>
                                    <td><?php echo e($user->name); ?></td>
                                    <td><?php echo e($user->email); ?></td>
                                    <td><?php echo e($user->phone ?? '-'); ?></td>
                                    <td>
                                        <span class="badge <?php echo e($user->is_active ? 'badge-success' : 'badge-danger'); ?>">
                                            <?php echo e($user->is_active ? 'Active' : 'Inactive'); ?>

                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <?php echo $__env->make('master.groups.partials.empty-state', ['icon' => 'users', 'label' => 'No users found'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <h5 class="mb-4 pb-3 border-bottom">Crew Members (<?php echo e($group->crewMembers->count()); ?>)</h5>

                    <?php if($group->crewMembers->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Position</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $group->crewMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $crew): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($index + 1); ?></td>
                                    <td><?php echo e($crew->name); ?></td>
                                    <td><?php echo e($crew->position ?? '-'); ?></td>
                                    <td><?php echo e($crew->phone ?? '-'); ?></td>
                                    <td>
                                        <span class="badge <?php echo e($crew->is_active ? 'badge-success' : 'badge-danger'); ?>">
                                            <?php echo e($crew->is_active ? 'Active' : 'Inactive'); ?>

                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <?php echo $__env->make('master.groups.partials.empty-state', ['icon' => 'user-check', 'label' => 'No crew members found'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <h5 class="mb-4 pb-3 border-bottom">Contacts (<?php echo e($group->contacts->count()); ?>)</h5>

                    <?php if($group->contacts->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>WhatsApp</th>
                                    <th>Position</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $group->contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($index + 1); ?></td>
                                    <td>
                                        <a href="<?php echo e(route('contacts.show', $contact)); ?>" class="text-decoration-none fw-semibold">
                                            <?php echo e($contact->name); ?>

                                        </a>
                                    </td>
                                    <td>
                                        <a href="https://wa.me/<?php echo e($contact->whatsapp_number); ?>"
                                           target="_blank"
                                           class="d-inline-flex align-items-center gap-1 text-decoration-none"
                                           style="color:#25D366;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                                            </svg>
                                            <?php echo e($contact->whatsapp_number); ?>

                                        </a>
                                    </td>
                                    <td><?php echo e($contact->position ?? '-'); ?></td>
                                    <td>
                                        <span class="badge <?php echo e($contact->is_active ? 'badge-success' : 'badge-danger'); ?>">
                                            <?php echo e($contact->is_active ? 'Active' : 'Inactive'); ?>

                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <?php echo $__env->make('master.groups.partials.empty-state', ['icon' => 'phone', 'label' => 'No contacts found'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <h5 class="mb-4 pb-3 border-bottom">Contracts (<?php echo e($group->contracts->count()); ?>)</h5>

                    <?php if($group->contracts->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Contractor Name</th>
                                    <th>SAP No</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $group->contracts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $contract): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($index + 1); ?></td>
                                    <td><?php echo e($contract->nama_kontraktor); ?></td>
                                    <td><?php echo e($contract->sap_no ?? '-'); ?></td>
                                    <td><?php echo e($contract->alamat_email ?? '-'); ?></td>
                                    <td><?php echo e($contract->no_tlp_kantor ?? '-'); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <?php echo $__env->make('master.groups.partials.empty-state', ['icon' => 'file-text', 'label' => 'No contracts found'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/master/groups/show.blade.php ENDPATH**/ ?>