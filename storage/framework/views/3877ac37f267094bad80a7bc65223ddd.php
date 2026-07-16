<?php
    // Logika Data Master
    $isDataMasterActive = request()->routeIs('users.*') ||
                          request()->routeIs('groups.*') ||
                          request()->routeIs('contacts.*') ||
                          request()->routeIs('companies.*') ||
                          request()->routeIs('contracts.*') ||
                          request()->routeIs('vessels.*') ||
                          request()->routeIs('crew.*') ||
                          request()->routeIs('entity-functions.*') ||
                          request()->routeIs('areas.*') ||
                          request()->routeIs('unsafe-acts.*') ||
                          request()->routeIs('unsafe-conditions.*') ||
                          request()->routeIs('pelanggaran.*') ||
                          request()->routeIs('security-event-categories.*') ||
                          request()->routeIs('action-categories.*') ||
                          request()->routeIs('cover-templates.*') ||
                          request()->routeIs('roles.*');

    $isBroadcastActive  = request()->routeIs('broadcast.*');

    // KPI HSSE — aktif untuk semua sub-route kpi-hsse.*
    $isKpiHsseActive    = request()->routeIs('kpi-hsse.*');
    $isKpiDashboard     = request()->routeIs('kpi-hsse.dashboard');
    $isKpiList          = $isKpiHsseActive && !$isKpiDashboard;
?>

<ul class="list-unstyled menu-categories" id="accordionExample">

    
    <li class="menu menu-heading">
        <div class="heading">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-minus"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>DASHBOARD</span>
        </div>
    </li>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view cermat dashboard')): ?>
    <li class="menu <?php echo e(request()->routeIs('dashboard.analytics') ? 'active' : ''); ?>">
        <a href="<?php echo e(route('dashboard.analytics')); ?>" class="dropdown-toggle">
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-home"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                <span>Dashboard Cermat</span>
            </div>
        </a>
    </li>
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage campaign salman')): ?>
    <li class="menu <?php echo e(request()->routeIs('dashboard.campaign-salman') ? 'active' : ''); ?>">
        <a href="<?php echo e(route('dashboard.campaign-salman')); ?>" class="dropdown-toggle">
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-pie-chart"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
                <span>Dashboard Campaign</span>
            </div>
        </a>
    </li>
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view dcu dashboard')): ?>
    <li class="menu <?php echo e(request()->routeIs('dashboard.health-check') ? 'active' : ''); ?>">
        <a href="<?php echo e(route('dashboard.health-check')); ?>" class="dropdown-toggle">
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bar-chart-2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                <span>Dashboard Health</span>
            </div>
        </a>
    </li>
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage hsse evaluation')): ?>
    <li class="menu <?php echo e(request()->routeIs('hsse-evaluation.dashboard') ? 'active' : ''); ?>">
        <a href="<?php echo e(route('hsse-evaluation.dashboard')); ?>" class="dropdown-toggle">
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-anchor"><circle cx="12" cy="5" r="3"></circle><line x1="12" y1="22" x2="12" y2="8"></line><path d="M5 12H2a10 10 0 0 0 20 0h-3"></path></svg>
                <span>Dashboard Eval</span>
            </div>
        </a>
    </li>
    <?php endif; ?>

    
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage kpi hsse')): ?>
    <li class="menu <?php echo e($isKpiDashboard ? 'active' : ''); ?>">
        <a href="<?php echo e(route('kpi-hsse.dashboard')); ?>" class="dropdown-toggle">
            <div class="d-flex align-items-center gap-2 w-100">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trending-up flex-shrink-0"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                <span>Dashboard KPI</span>
                <?php
                    $pendingKpi = \App\Models\KpiReport::where('status','submitted')->count();
                ?>
                <?php if($pendingKpi > 0 && auth()->user()->hasAnyRole(['hsse','super-admin'])): ?>
                <span class="badge badge-danger rounded-pill ms-auto" style="font-size:.65rem;padding:2px 7px;"><?php echo e($pendingKpi); ?></span>
                <?php endif; ?>
            </div>
        </a>
    </li>
    <?php endif; ?>
    

    <?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'super-admin|hsse')): ?>
    <li class="menu <?php echo e(request()->routeIs('cermat.action-monitoring.*') ? 'active' : ''); ?>">
        <a href="<?php echo e(route('cermat.action-monitoring.index')); ?>" class="dropdown-toggle">
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                <span>Action Monitoring</span>
            </div>
        </a>
    </li>
    <?php endif; ?>

    
    <li class="menu menu-heading">
        <div class="heading">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-minus"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>ACTIVITY</span>
        </div>
    </li>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage cermat')): ?>
    <li class="menu <?php echo e(request()->routeIs('cermat.reports.*') ? 'active' : ''); ?>">
        <a href="<?php echo e(route('cermat.reports.index')); ?>" class="dropdown-toggle">
            <div class="d-flex justify-content-between align-items-center w-100">
                <div class="d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    <span class="ms-2">Cermat/Teman</span>
                </div>
                <?php if(isset($pendingCermatCount) && $pendingCermatCount > 0): ?>
                <span class="badge badge-danger" style="border-radius:50%;padding:4px 8px;font-size:11px;"><?php echo e($pendingCermatCount); ?></span>
                <?php endif; ?>
            </div>
        </a>
    </li>
    <?php endif; ?>

    
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage kpi hsse')): ?>
    <li class="menu <?php echo e($isKpiList ? 'active' : ''); ?>">
        <a href="<?php echo e(route('kpi-hsse.index')); ?>" class="dropdown-toggle">
            <div class="d-flex justify-content-between align-items-center w-100">
                <div class="d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clipboard flex-shrink-0"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
                    <span>KPI HSSE</span>
                </div>
                <?php if(isset($pendingKpi) && $pendingKpi > 0 && auth()->user()->hasAnyRole(['hsse','super-admin'])): ?>
                <span class="badge badge-warning text-dark" style="border-radius:50%;padding:4px 8px;font-size:11px;"><?php echo e($pendingKpi); ?></span>
                <?php endif; ?>
            </div>
        </a>
    </li>
    <?php endif; ?>
    

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage campaign salman')): ?>
    <li class="menu <?php echo e(request()->routeIs('campaign-salman.*') ? 'active' : ''); ?>">
        <a href="<?php echo e(route('campaign-salman.index')); ?>" class="dropdown-toggle">
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layers"><polygon points="12 2 2 7 2 17 12 22 22 17 22 7 12 2"></polygon><polyline points="2 7 12 12 22 7"></polyline><polyline points="12 12 12 22"></polyline></svg>
                <span>Campaign</span>
            </div>
        </a>
    </li>
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage hsse evaluation')): ?>
    <li class="menu <?php echo e(request()->routeIs('hsse-evaluation.*') && !request()->routeIs('hsse-evaluation.dashboard') ? 'active' : ''); ?>">
        <a href="<?php echo e(route('hsse-evaluation.index')); ?>" class="dropdown-toggle">
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shield"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                <span>Crew Evaluation</span>
            </div>
        </a>
    </li>
    <?php endif; ?>

    
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['view crew assessment', 'manage crew assessment'])): ?>
    <li class="menu <?php echo e(request()->routeIs('crew-assessment.*') ? 'active' : ''); ?>">
        <a href="<?php echo e(route('crew-assessment.index')); ?>" class="dropdown-toggle">
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user-check"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>
                <span>Crew Assessment</span>
            </div>
        </a>
    </li>
    <?php endif; ?>
    

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage my action')): ?>
    <li class="menu <?php echo e(request()->routeIs('user.my-actions.*') ? 'active' : ''); ?>">
        <a href="<?php echo e(route('user.my-actions.index')); ?>" class="dropdown-toggle">
            <div class="d-flex justify-content-between align-items-center w-100">
                <div class="d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-square"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                    <span class="ms-2">My Action Items</span>
                </div>
                <?php if(isset($myActionCount) && $myActionCount > 0): ?>
                <span class="badge badge-warning text-white" style="border-radius:50%;padding:4px 8px;font-size:11px;"><?php echo e($myActionCount); ?></span>
                <?php endif; ?>
            </div>
        </a>
    </li>
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage daily checkup')): ?>
    <li class="menu <?php echo e(request()->routeIs('daily-checkup.*') ? 'active' : ''); ?>">
        <a href="<?php echo e(route('daily-checkup.index')); ?>" class="dropdown-toggle">
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clipboard"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
                <span>My Daily Checkup</span>
            </div>
        </a>
    </li>
    <?php endif; ?>

    
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage broadcast')): ?>
    <li class="menu menu-heading">
        <div class="heading">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-minus"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Broadcast</span>
        </div>
    </li>
    <li class="menu <?php echo e($isBroadcastActive ? 'active' : ''); ?>">
        <a href="#broadcastMenu" data-bs-toggle="collapse" aria-expanded="<?php echo e($isBroadcastActive ? 'true' : 'false'); ?>" class="dropdown-toggle">
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-send"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                <span>Broadcast</span>
            </div>
            <div><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg></div>
        </a>
        <ul class="collapse submenu list-unstyled <?php echo e($isBroadcastActive ? 'show' : ''); ?>" id="broadcastMenu" data-bs-parent="#accordionExample">
            <li class="<?php echo e(request()->routeIs('broadcast.create.manual') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('broadcast.create.manual')); ?>">Manual</a>
            </li>
            <li class="<?php echo e(request()->routeIs('broadcast.create.group-contact') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('broadcast.create.group-contact')); ?>">Group Contact</a>
            </li>
            <li class="<?php echo e(request()->routeIs('broadcast.history') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('broadcast.history')); ?>">History</a>
            </li>
        </ul>
    </li>
    <?php endif; ?>

    
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['manage roles','manage users','manage cover templates','manage contacts','manage groups',
             'manage companies','manage contracts','manage vessels','manage entity functions','manage areas',
             'manage unsafe acts','manage unsafe conditions','manage pelanggaran',
             'manage security event categories','manage action categories'])): ?>
    <li class="menu menu-heading">
        <div class="heading">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-minus"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>DATA MASTER</span>
        </div>
    </li>
    <li class="menu <?php echo e($isDataMasterActive ? 'active' : ''); ?>">
        <a href="#dataMaster" data-bs-toggle="collapse" aria-expanded="<?php echo e($isDataMasterActive ? 'true' : 'false'); ?>" class="dropdown-toggle">
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-server"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line></svg>
                <span>Data Master</span>
            </div>
            <div><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg></div>
        </a>
        <ul class="collapse submenu list-unstyled <?php echo e($isDataMasterActive ? 'show' : ''); ?>" id="dataMaster" data-bs-parent="#accordionExample">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage roles')): ?><li class="<?php echo e(request()->routeIs('roles.*') ? 'active' : ''); ?>"><a href="<?php echo e(route('roles.index')); ?>">Roles</a></li><?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage users')): ?><li class="<?php echo e(request()->routeIs('users.*') ? 'active' : ''); ?>"><a href="<?php echo e(route('users.index')); ?>">Users</a></li><?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage cover templates')): ?><li class="<?php echo e(request()->routeIs('cover-templates.*') ? 'active' : ''); ?>"><a href="<?php echo e(route('cover-templates.index')); ?>">Cover Report</a></li><?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage groups')): ?><li class="<?php echo e(request()->routeIs('groups.*') ? 'active' : ''); ?>"><a href="<?php echo e(route('groups.index')); ?>">Group Contact</a></li><?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage contacts')): ?><li class="<?php echo e(request()->routeIs('contacts.*') ? 'active' : ''); ?>"><a href="<?php echo e(route('contacts.index')); ?>">Contacts</a></li><?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage companies')): ?><li class="<?php echo e(request()->routeIs('companies.*') ? 'active' : ''); ?>"><a href="<?php echo e(route('companies.index')); ?>">Companies</a></li><?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage contracts')): ?><li class="<?php echo e(request()->routeIs('contracts.*') ? 'active' : ''); ?>"><a href="<?php echo e(route('contracts.index')); ?>">Contracts</a></li><?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage vessels')): ?><li class="<?php echo e(request()->routeIs('vessels.*') ? 'active' : ''); ?>"><a href="<?php echo e(route('vessels.index')); ?>">Vessels / Unit</a></li><?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage vessels')): ?><li class="<?php echo e(request()->routeIs('crew.*') ? 'active' : ''); ?>"><a href="<?php echo e(route('crew.index')); ?>">Crew</a></li><?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage entity functions')): ?><li class="<?php echo e(request()->routeIs('entity-functions.*') ? 'active' : ''); ?>"><a href="<?php echo e(route('entity-functions.index')); ?>">Entity Functions</a></li><?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage areas')): ?><li class="<?php echo e(request()->routeIs('areas.*') ? 'active' : ''); ?>"><a href="<?php echo e(route('areas.index')); ?>">Areas</a></li><?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage unsafe acts')): ?><li class="<?php echo e(request()->routeIs('unsafe-acts.*') ? 'active' : ''); ?>"><a href="<?php echo e(route('unsafe-acts.index')); ?>">Unsafe Acts</a></li><?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage unsafe conditions')): ?><li class="<?php echo e(request()->routeIs('unsafe-conditions.*') ? 'active' : ''); ?>"><a href="<?php echo e(route('unsafe-conditions.index')); ?>">Unsafe Conditions</a></li><?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage pelanggaran')): ?><li class="<?php echo e(request()->routeIs('pelanggaran.*') ? 'active' : ''); ?>"><a href="<?php echo e(route('pelanggaran.index')); ?>">Pelanggaran</a></li><?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage security event categories')): ?><li class="<?php echo e(request()->routeIs('security-event-categories.*') ? 'active' : ''); ?>"><a href="<?php echo e(route('security-event-categories.index')); ?>">Security Categories</a></li><?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage action categories')): ?><li class="<?php echo e(request()->routeIs('action-categories.*') ? 'active' : ''); ?>"><a href="<?php echo e(route('action-categories.index')); ?>">Action Categories</a></li><?php endif; ?>
        </ul>
    </li>
    <?php endif; ?>

    
    <li class="menu menu-heading">
        <div class="heading">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-minus"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>SETTINGS</span>
        </div>
    </li>
    <li class="menu <?php echo e(request()->routeIs('profile.*') ? 'active' : ''); ?>">
        <a href="<?php echo e(route('profile.index')); ?>" class="dropdown-toggle">
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                <span>Profile</span>
            </div>
        </a>
    </li>
    <li class="menu">
        <a href="<?php echo e(route('logout')); ?>" class="dropdown-toggle"
           onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                <span>Logout</span>
            </div>
        </a>
        <form id="logout-form-sidebar" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none"><?php echo csrf_field(); ?></form>
    </li>

</ul>
<?php /**PATH /home/kaptensa/salman/resources/views/components/menu.blade.php ENDPATH**/ ?>