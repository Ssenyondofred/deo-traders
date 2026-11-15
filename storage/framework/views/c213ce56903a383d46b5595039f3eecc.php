<?php $__env->startSection('title', 'Purchase ' . $purchase->purchase_number); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Purchase</h2>
        <p class="text-sm text-slate-500"><?php echo e($purchase->purchase_number); ?></p>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo e(route('purchases.edit', $purchase)); ?>" class="btn-primary-soft">Edit</a>
        <a href="<?php echo e(route('purchases.index')); ?>" class="px-4 py-2 text-sm text-slate-600 border border-slate-300 rounded-lg">Back</a>
    </div>
</div>

<div class="panel p-5 max-w-3xl">
    <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
        <div>
            <div class="text-slate-500">Supplier</div>
            <div class="font-medium"><?php echo e($purchase->supplier->name ?? '—'); ?></div>
        </div>
        <div>
            <div class="text-slate-500">Date</div>
            <div class="font-medium"><?php echo e($purchase->purchase_date ? $purchase->purchase_date->format('d M Y') : $purchase->created_at->format('d M Y')); ?></div>
        </div>
        <div>
            <div class="text-slate-500">Status</div>
            <div class="font-medium"><?php echo e(ucfirst($purchase->status)); ?></div>
        </div>
        <div>
            <div class="text-slate-500">Number</div>
            <div class="font-medium"><?php echo e($purchase->purchase_number); ?></div>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-4">
        <div class="flex justify-between text-sm mb-1">
            <span class="text-slate-500">Total</span>
            <span class="font-medium">UGX <?php echo e(number_format($purchase->total, 0)); ?></span>
        </div>
        <div class="flex justify-between text-sm mb-1">
            <span class="text-slate-500">Paid</span>
            <span class="font-medium">UGX <?php echo e(number_format($purchase->paid, 0)); ?></span>
        </div>
        <div class="flex justify-between text-sm font-semibold text-red-600">
            <span>Balance</span>
            <span>UGX <?php echo e(number_format($purchase->balance, 0)); ?></span>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Admin\Desktop\Patu\deo-traders\resources\views/purchases/show.blade.php ENDPATH**/ ?>