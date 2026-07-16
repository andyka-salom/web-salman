<?php $__env->startSection('title', 'Hierarchy Diagram'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    /* VIEWPORT: Jendela tempat melihat diagram */
    .tree-view-container {
        width: 100%;
        height: 750px;
        overflow: auto; /* Scrollbar otomatis muncul */
        background-color: #f8f9fa;
        border: 1px solid #e0e6ed;
        border-radius: 8px;
        cursor: grab;
        position: relative;
        display: block; /* Hindari flex pada container utama */
    }

    .tree-view-container:active {
        cursor: grabbing;
    }

    /* CANVAS: Area luas tempat diagram digambar */
    .tree-canvas {
        padding: 150px; /* Ruang ekstra agar bisa di-drag jauh ke segala arah */
        min-width: 100%;
        min-height: 100%;
        display: inline-block;
        vertical-align: top;
        transform-origin: 0 0; /* Zoom dari pojok kiri atas agar koordinat scroll tetap sinkron */
        transition: transform 0.1s ease-out;
    }

    /* TREE LOGIC: Menggunakan display table agar lebar dinamis mengikuti anak */
    .tree {
        display: table; /* KUNCI UTAMA: Mengikuti lebar konten secara otomatis */
        margin: 0 auto; /* Tengah jika kecil, tetap bisa discroll jika besar */
    }

    .tree ul {
        padding-top: 40px;
        position: relative;
        display: flex; /* Anak-anak sejajar secara horizontal */
        justify-content: center;
        padding-left: 0;
        margin-bottom: 0;
    }

    .tree li {
        text-align: center;
        list-style-type: none;
        position: relative;
        padding: 40px 10px 0 10px;
        transition: all 0.5s;
    }

    /* Garis Penghubung (Connectors) */
    .tree li::before, .tree li::after {
        content: '';
        position: absolute; top: 0; right: 50%;
        border-top: 2px solid #ccc;
        width: 50%; height: 40px;
    }
    .tree li::after {
        right: auto; left: 50%;
        border-left: 2px solid #ccc;
    }
    .tree li:only-child::after, .tree li:only-child::before { display: none; }
    .tree li:only-child { padding-top: 0; }
    .tree li:first-child::before, .tree li:last-child::after { border: 0 none; }
    .tree li:last-child::before { border-right: 2px solid #ccc; border-radius: 0 5px 0 0; }
    .tree li:first-child::after { border-radius: 5px 0 0 0; }
    .tree ul ul::before {
        content: '';
        position: absolute; top: 0; left: 50%;
        border-left: 2px solid #ccc;
        width: 0; height: 40px;
    }

    /* Style Kotak Node */
    .tree-node {
        border: 1px solid #e0e6ed;
        background: #fff;
        padding: 12px 15px;
        color: #3b3f5c;
        font-family: 'Nunito', sans-serif;
        display: inline-block;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        min-width: 160px;
        position: relative;
        z-index: 10;
    }
    .tree-node:hover {
        border-color: #1b55e2;
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .node-code { font-size: 10px; font-weight: bold; background: #f1f2f3; padding: 2px 6px; border-radius: 4px; display: block; margin-bottom: 5px; width: fit-content; margin-left: auto; margin-right: auto; }
    .node-title { font-weight: 800; display: block; font-size: 14px; margin-bottom: 5px; white-space: normal; width: 150px; line-height: 1.2;}
    .node-users { font-size: 11px; color: #1b55e2; display: block; border-top: 1px solid #eee; padding-top: 5px; }

    .root-node { background: #eef2ff; border: 2px solid #4361ee; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">

        <div class="row layout-top-spacing mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h3>Organization Hierarchy</h3>
                <div>
                    <button class="btn btn-outline-dark" onclick="zoom(0.1)">Zoom In (+)</button>
                    <button class="btn btn-outline-dark" onclick="zoom(-0.1)">Zoom Out (-)</button>
                    <button class="btn btn-secondary" onclick="resetView()">Reset</button>
                    <a href="<?php echo e(route('entity-functions.index')); ?>" class="btn btn-dark">Back</a>
                </div>
            </div>
        </div>

        <div class="widget-content widget-content-area br-8 p-0">
            <div class="tree-view-container" id="viewport">
                <div class="tree-canvas" id="canvas">
                    <div class="tree" id="treeRoot">
                        <?php
                            function renderTree($parentId, $allNodes) {
                                $children = $allNodes->where('parent_id', $parentId);
                                if ($children->count() > 0) {
                                    echo '<ul>';
                                    foreach ($children as $node) {
                                        echo '<li>';
                                        echo '<div class="tree-node">';
                                        echo '<span class="node-code">' . $node->code . '</span>';
                                        echo '<span class="node-title">' . $node->name . '</span>';
                                        echo '<span class="node-users">' . $node->users_count . ' Users</span>';
                                        echo '</div>';
                                        renderTree($node->id, $allNodes);
                                        echo '</li>';
                                    }
                                    echo '</ul>';
                                }
                            }
                        ?>

                        <ul>
                            <?php $__currentLoopData = $nodes->whereNull('parent_id'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $root): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <div class="tree-node root-node">
                                    <span class="node-code text-primary"><?php echo e($root->code); ?></span>
                                    <span class="node-title text-primary"><?php echo e($root->name); ?></span>
                                    <span class="node-users"><?php echo e($root->users_count); ?> Users</span>
                                </div>
                                <?php echo e(renderTree($root->id, $nodes)); ?>

                            </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    const viewport = document.getElementById('viewport');
    const canvas = document.getElementById('canvas');
    const treeRoot = document.getElementById('treeRoot');

    let isDown = false;
    let startX, startY, scrollLeft, scrollTop;
    let currentScale = 1;

    // --- FUNGSI DRAG ---
    viewport.addEventListener('mousedown', (e) => {
        isDown = true;
        viewport.style.cursor = 'grabbing';
        startX = e.pageX - viewport.offsetLeft;
        startY = e.pageY - viewport.offsetTop;
        scrollLeft = viewport.scrollLeft;
        scrollTop = viewport.scrollTop;
    });

    viewport.addEventListener('mouseleave', () => {
        isDown = false;
        viewport.style.cursor = 'grab';
    });

    viewport.addEventListener('mouseup', () => {
        isDown = false;
        viewport.style.cursor = 'grab';
    });

    viewport.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - viewport.offsetLeft;
        const y = e.pageY - viewport.offsetTop;
        const walkX = (x - startX) * 1.5;
        const walkY = (y - startY) * 1.5;
        viewport.scrollLeft = scrollLeft - walkX;
        viewport.scrollTop = scrollTop - walkY;
    });

    // --- FUNGSI ZOOM ---
    function zoom(delta) {
        currentScale += delta;
        currentScale = Math.min(Math.max(0.3, currentScale), 2); // Limit zoom 0.3x - 2x
        canvas.style.transform = `scale(${currentScale})`;
    }

    function resetView() {
        currentScale = 1;
        canvas.style.transform = `scale(1)`;
        centerDiagram();
    }

    // --- FUNGSI MENENGAHKAN DIAGRAM ---
    function centerDiagram() {
        // Beri sedikit jeda agar DOM terhitung sempurna
        setTimeout(() => {
            const canvasWidth = treeRoot.offsetWidth;
            const viewportWidth = viewport.offsetWidth;
            // Hitung posisi tengah, tambahkan padding canvas (150px)
            const scrollCenter = (canvasWidth / 2) + 150 - (viewportWidth / 2);
            viewport.scrollLeft = scrollCenter;
        }, 100);
    }

    // Jalankan center saat pertama kali load
    window.onload = centerDiagram;
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/master/entity-functions/hierarchy.blade.php ENDPATH**/ ?>