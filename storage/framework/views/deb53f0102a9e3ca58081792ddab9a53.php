

<?php $__env->startSection('title', 'Suppliers'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Suppliers</h2>
        <p class="text-sm text-slate-500 mt-1">Manage your suppliers</p>
    </div>
    <a href="<?php echo e(route('suppliers.create')); ?>" class="btn-primary-soft">+ Add Supplier</a>
</div>

<div class="panel overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Phone</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php $__empty_1 = true; $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-medium text-slate-800"><?php echo e($supplier->name); ?></td>
                    <td class="px-4 py-3 text-slate-500"><?php echo e($supplier->phone ?? '—'); ?></td>
                    <td class="px-4 py-3 text-slate-500"><?php echo e($supplier->email ?? '—'); ?></td>
                    <td class="px-4 py-3">
                        <?php if($supplier->is_active ?? true): ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Active</span>
                        <?php else: ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="<?php echo e(route('suppliers.edit', $supplier)); ?>" class="text-blue-600 hover:text-blue-800 font-medium mr-3">Edit</a>
                        <form action="<?php echo e(route('suppliers.destroy', $supplier)); ?>" method="POST" class="inline" onsubmit="return confirm('Delete this supplier?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-slate-500">
                        No suppliers yet. <a href="<?php echo e(route('suppliers.create')); ?>" class="text-blue-600 hover:underline">Create one</a>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if(method_exists($suppliers, 'links')): ?>
    <div class="px-4 py-3 border-t border-slate-100"><?php echo e($suppliers->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Admin\Desktop\Patu\deo-traders\resources\views/suppliers/index.blade.php ENDPATH**/ ?>