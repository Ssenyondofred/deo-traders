<?php $__env->startSection('title', 'Purchases'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Purchases</h2>
        <p class="text-sm text-slate-500 mt-1">All purchase orders</p>
    </div>
    <a href="<?php echo e(route('purchases.create')); ?>" class="btn-primary-soft">+ Add Purchase</a>
</div>

<div class="panel p-5">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Number</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Supplier</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Date</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">Total</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">Paid</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">Balance</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $purchases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purchase): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="border-b border-slate-100">
                        <td class="px-3 py-2"><?php echo e($purchase->purchase_number); ?></td>
                        <td class="px-3 py-2"><?php echo e($purchase->supplier->name ?? '—'); ?></td>
                        <td class="px-3 py-2"><?php echo e($purchase->purchase_date ? $purchase->purchase_date->format('d M Y') : $purchase->created_at->format('d M Y')); ?></td>
                        <td class="px-3 py-2 text-right"><?php echo e(number_format($purchase->total, 0)); ?></td>
                        <td class="px-3 py-2 text-right"><?php echo e(number_format($purchase->paid, 0)); ?></td>
                        <td class="px-3 py-2 text-right font-semibold text-red-600"><?php echo e(number_format($purchase->balance, 0)); ?></td>
                        <td class="px-3 py-2 text-right">
                            <a href="<?php echo e(route('purchases.edit', $purchase)); ?>" class="text-slate-600 text-xs">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="px-3 py-6 text-center text-slate-500">No purchases yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-4"><?php echo e($purchases->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Admin\Desktop\Patu\deo-traders\resources\views/purchases/index.blade.php ENDPATH**/ ?>