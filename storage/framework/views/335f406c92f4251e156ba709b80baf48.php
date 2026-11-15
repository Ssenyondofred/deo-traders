<?php $__env->startSection('title', 'Sales'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Sales</h2>
        <p class="text-sm text-slate-500 mt-1">All sales invoices</p>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo e(route('sales.trash')); ?>" class="btn-primary-soft" style="background:#64748b;">Trash</a>
        <a href="<?php echo e(route('sales.create')); ?>" class="btn-primary-soft">+ Add Sale</a>
    </div>
</div>

<div class="panel p-5">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-3 py-2 text-left">Invoice</th>
                <th class="px-3 py-2 text-left">Customer</th>
                <th class="px-3 py-2 text-left">Date</th>
                <th class="px-3 py-2 text-right">Total</th>
                <th class="px-3 py-2 text-right">Paid</th>
                <th class="px-3 py-2 text-right">Balance</th>
                <th class="px-3 py-2 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-b border-slate-100">
                    <td class="px-3 py-2"><a href="<?php echo e(route('sales.show', $sale)); ?>" class="text-blue-600"><?php echo e($sale->invoice_number); ?></a></td>
                    <td class="px-3 py-2"><?php echo e($sale->customer_name ?: ($sale->customer->name ?? 'Walk-in')); ?></td>
                    <td class="px-3 py-2"><?php echo e(optional($sale->created_at)->timezone('Africa/Kampala')->format('d M Y')); ?></td>
                    <td class="px-3 py-2 text-right"><?php echo e(number_format($sale->total, 0)); ?></td>
                    <td class="px-3 py-2 text-right"><?php echo e(number_format($sale->paid, 0)); ?></td>
                    <td class="px-3 py-2 text-right font-semibold text-red-600"><?php echo e(number_format($sale->balance, 0)); ?></td>
                    <td class="px-3 py-2 text-right">
                        <a href="<?php echo e(route('sales.show', $sale)); ?>" class="text-blue-600 text-xs">Invoice</a>
                        <a href="<?php echo e(route('sales.edit', $sale)); ?>" class="text-slate-600 text-xs ml-2">Edit</a>
                        <form action="<?php echo e(route('sales.destroy', $sale)); ?>" method="POST" class="inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="text-red-600 text-xs ml-2" onclick="return confirm('Delete?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" class="px-3 py-6 text-center text-slate-500">No sales yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="mt-4"><?php echo e($sales->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Admin\Desktop\Patu\deo-traders\resources\views/sales/index.blade.php ENDPATH**/ ?>