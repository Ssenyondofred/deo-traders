<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-900">Dashboard</h2>
    <p class="text-sm text-slate-500 mt-1">Overview of sales, purchases, stock and people</p>
</div>


<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="kpi" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
        <div class="label">Today's Sales</div>
        <div class="value">UGX <?php echo e(number_format($todaySales ?? 0, 0)); ?></div>
        <div class="hint">All sales today</div>
    </div>
    <div class="kpi" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);">
        <div class="label">This Month Sales</div>
        <div class="value">UGX <?php echo e(number_format($monthSales ?? 0, 0)); ?></div>
        <div class="hint"><?php echo e(now()->format('F Y')); ?></div>
    </div>
    <div class="kpi" style="background:linear-gradient(135deg,#ef4444,#b91c1c);">
        <div class="label">Debtors (Sales)</div>
        <div class="value">UGX <?php echo e(number_format($totalDebt ?? 0, 0)); ?></div>
        <div class="hint">Unpaid customer balances</div>
    </div>
    <div class="kpi" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
        <div class="label">Creditors (Purchases)</div>
        <div class="value">UGX <?php echo e(number_format($outstanding ?? 0, 0)); ?></div>
        <div class="hint">Unpaid supplier balances</div>
    </div>
</div>


<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="kpi" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);">
        <div class="label">Today's Purchases</div>
        <div class="value">UGX <?php echo e(number_format($todayPurchases ?? 0, 0)); ?></div>
        <div class="hint">All purchases today</div>
    </div>
    <div class="kpi" style="background:linear-gradient(135deg,#14b8a6,#0f766e);">
        <div class="label">This Month Purchases</div>
        <div class="value">UGX <?php echo e(number_format($monthPurchases ?? 0, 0)); ?></div>
        <div class="hint"><?php echo e(now()->format('F Y')); ?></div>
    </div>
    <div class="kpi" style="background:linear-gradient(135deg,#10b981,#059669);">
        <div class="label">Low Stock</div>
        <div class="value"><?php echo e($lowStock ?? 0); ?></div>
        <div class="hint">Items with qty ≤ 10</div>
    </div>
    <div class="kpi" style="background:linear-gradient(135deg,#64748b,#334155);">
        <div class="label">Products</div>
        <div class="value"><?php echo e($totalProducts ?? 0); ?></div>
        <div class="hint">In catalog</div>
    </div>
</div>


<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="panel p-4">
        <div class="text-xs text-slate-500 font-medium">Products</div>
        <div class="text-2xl font-bold text-slate-900 mt-1"><?php echo e($totalProducts ?? 0); ?></div>
    </div>
    <div class="panel p-4">
        <div class="text-xs text-slate-500 font-medium">Customers</div>
        <div class="text-2xl font-bold text-slate-900 mt-1"><?php echo e($totalCustomers ?? 0); ?></div>
    </div>
    <div class="panel p-4">
        <div class="text-xs text-slate-500 font-medium">Suppliers</div>
        <div class="text-2xl font-bold text-slate-900 mt-1"><?php echo e($totalSuppliers ?? 0); ?></div>
    </div>
    <div class="panel p-4">
        <div class="text-xs text-slate-500 font-medium">Categories</div>
        <div class="text-2xl font-bold text-slate-900 mt-1"><?php echo e($totalCategories ?? 0); ?></div>
    </div>
</div>


<div class="panel p-5 mb-6">
    <div class="flex items-center justify-between mb-3">
        <h3 class="font-semibold text-slate-800">Outstanding Debtors</h3>
        <span class="text-sm font-bold text-red-600">
            Total: UGX <?php echo e(number_format($totalDebt ?? 0, 0)); ?>

        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Invoice</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Customer</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Phone</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Date</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">Total</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">Paid</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = ($debtors ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="border-b border-slate-100">
                        <td class="px-3 py-2">
                            <a href="<?php echo e(route('sales.show', $d)); ?>" class="text-blue-600 font-medium hover:underline">
                                <?php echo e($d->invoice_number ?? '—'); ?>

                            </a>
                        </td>
                        <td class="px-3 py-2"><?php echo e($d->customer_name ?: ($d->customer->name ?? 'Walk-in')); ?></td>
                        <td class="px-3 py-2"><?php echo e($d->customer->phone ?? '—'); ?></td>
                        <td class="px-3 py-2"><?php echo e(optional($d->created_at)->timezone('Africa/Kampala')->format('d M Y')); ?></td>
                        <td class="px-3 py-2 text-right"><?php echo e(number_format($d->total ?? 0, 0)); ?></td>
                        <td class="px-3 py-2 text-right"><?php echo e(number_format($d->paid ?? 0, 0)); ?></td>
                        <td class="px-3 py-2 text-right font-semibold text-red-600"><?php echo e(number_format($d->balance ?? 0, 0)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="px-3 py-6 text-center text-slate-500">
                            No outstanding debtors.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<div class="panel p-5">
    <div class="font-semibold text-slate-800 mb-3">Quick actions</div>
    <div class="flex flex-wrap gap-2">
        <a href="<?php echo e(route('sales.create')); ?>" class="btn-primary-soft" style="background:#2563eb;">+ Sale</a>
        <a href="<?php echo e(route('products.create')); ?>" class="btn-primary-soft">+ Product</a>
        <a href="<?php echo e(route('purchases.create')); ?>" class="btn-primary-soft" style="background:#0f766e;">+ Purchase</a>
        <a href="<?php echo e(route('customers.create')); ?>" class="btn-primary-soft" style="background:#7c3aed;">+ Customer</a>
        <a href="<?php echo e(route('suppliers.create')); ?>" class="btn-primary-soft" style="background:#b45309;">+ Supplier</a>
        <a href="<?php echo e(route('reports.index')); ?>" class="btn-primary-soft" style="background:#334155;">Reports</a>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Admin\Desktop\Patu\deo-traders\resources\views/dashboard.blade.php ENDPATH**/ ?>