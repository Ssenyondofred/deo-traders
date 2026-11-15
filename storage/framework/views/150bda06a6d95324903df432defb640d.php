<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(config('app.name', 'Deo Traders')); ?> - <?php echo $__env->yieldContent('title', 'Dashboard'); ?></title>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        [x-cloak] { display: none !important; }
        .sidebar-link {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px; border-radius: 10px;
            color: #94a3b8; font-size: 13.5px; font-weight: 500;
            text-decoration: none; transition: all .15s;
        }
        .sidebar-link:hover { background: rgba(255,255,255,.06); color: #fff; }
        .sidebar-link.active {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
            box-shadow: 0 4px 12px rgba(37,99,235,.35);
        }
        .nav-label {
            font-size: 10px; font-weight: 700; letter-spacing: .08em;
            text-transform: uppercase; color: #64748b;
            padding: 16px 14px 6px;
        }
        .kpi {
            border-radius: 16px; padding: 20px; color: #fff;
            position: relative; overflow: hidden;
        }
        .kpi::after {
            content: ''; position: absolute; right: -12px; top: -12px;
            width: 80px; height: 80px; border-radius: 50%;
            background: rgba(255,255,255,.12);
        }
        .kpi .label { font-size: 12px; opacity: .9; font-weight: 500; }
        .kpi .value { font-size: 24px; font-weight: 700; margin-top: 6px; }
        .kpi .hint { font-size: 11px; opacity: .75; margin-top: 4px; }
        .panel {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
        }
        .btn-primary-soft {
            background: #2563eb; color: #fff; border: none; border-radius: 10px;
            padding: 8px 14px; font-size: 13px; font-weight: 600;
            text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-primary-soft:hover { background: #1d4ed8; color: #fff; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800">
<div class="min-h-screen flex" x-data="{ open: true }">

    
    <aside class="w-64 bg-slate-900 text-white flex-shrink-0 min-h-screen flex flex-col border-r border-slate-800"
           :class="open ? 'block' : 'hidden lg:block'">
        <div class="px-5 py-5 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-500 flex items-center justify-center font-bold text-sm">DT</div>
                <div>
                    <div class="font-semibold text-sm tracking-wide">Deo Traders</div>
                    <div class="text-[11px] text-slate-400">Stock & Business</div>
                </div>
            </div>
        </div>

               <nav class="flex-1 overflow-y-auto px-3 py-4">
            <a href="<?php echo e(route('dashboard')); ?>" class="sidebar-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                <span>📊</span> Dashboard
            </a>

            <div class="nav-label">Inventory</div>
            <a href="<?php echo e(route('categories.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('categories.*') ? 'active' : ''); ?>"><span>📁</span> Categories</a>
            <a href="<?php echo e(route('brands.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('brands.*') ? 'active' : ''); ?>"><span>🏷️</span> Brands</a>
            <a href="<?php echo e(route('units.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('units.*') ? 'active' : ''); ?>"><span>📏</span> Units</a>
            <a href="<?php echo e(route('products.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('products.*') ? 'active' : ''); ?>"><span>📦</span> Products</a>

            <div class="nav-label">People</div>
            <a href="<?php echo e(route('customers.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('customers.*') ? 'active' : ''); ?>"><span>👤</span> Customers</a>
            <a href="<?php echo e(route('suppliers.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('suppliers.*') ? 'active' : ''); ?>"><span>🏭</span> Suppliers</a>

            <div class="nav-label">Operations</div>
            <a href="<?php echo e(route('purchases.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('purchases.*') ? 'active' : ''); ?>"><span>🛒</span> Purchases</a>
            <a href="<?php echo e(route('purchases.daily')); ?>" class="sidebar-link <?php echo e(request()->routeIs('purchases.daily') ? 'active' : ''); ?>"><span>📄</span> Daily Receipt</a>
            <a href="<?php echo e(route('sales.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('sales.*') ? 'active' : ''); ?>"><span>💰</span> Sales</a>

            <div class="nav-label">Admin</div>
            <a href="<?php echo e(route('users.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('users.*') ? 'active' : ''); ?>"><span>👥</span> Users</a>
            <a href="<?php echo e(route('reports.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('reports.*') ? 'active' : ''); ?>"><span>📈</span> Reports</a>
        </nav>
        <div class="p-4 border-t border-slate-800 text-[11px] text-slate-500">
            © <?php echo e(date('Y')); ?> Deo Traders
        </div>
    </aside>

    
    <div class="flex-1 flex flex-col min-w-0">
        <header class="bg-white border-b border-slate-200 px-5 py-3 flex items-center justify-between sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <button type="button" class="lg:hidden p-2 rounded-lg border border-slate-200" @click="open = !open">☰</button>
                <h1 class="text-base font-semibold text-slate-800"><?php echo $__env->yieldContent('title', 'Dashboard'); ?></h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-slate-600 hidden sm:inline"><?php echo e(auth()->user()->name ?? ''); ?></span>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="text-sm text-red-600 font-medium hover:text-red-700">Logout</button>
                </form>
            </div>
        </header>

        <main class="flex-1 p-5 md:p-6">
            <?php if(session('success')): ?>
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>
</div>
<footer class="text-center text-xs text-slate-400 py-4 border-t border-slate-200 mt-auto">
    &copy; <?php echo e(date('Y')); ?> Deo Traders. Developed by <strong>Ssenyondo Fred</strong> � 
    <a href="mailto:ssenyondofred4@gmail.com" class="text-blue-500">ssenyondofred4@gmail.com</a>
</footer>
</body>
</html>
<?php /**PATH C:\Users\Admin\Desktop\Patu\deo-traders\resources\views/layouts/admin.blade.php ENDPATH**/ ?>