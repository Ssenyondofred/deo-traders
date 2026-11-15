<?php $__env->startSection('title', 'Add Sale'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-900">Add Sale</h2>
    <p class="text-sm text-slate-500 mt-1">Create a new sales invoice</p>
</div>

<div class="panel p-5 max-w-4xl">
    <form action="<?php echo e(route('sales.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Customer</label>
                <select name="customer_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">— Walk-in —</option>
                    <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($customer->id); ?>"><?php echo e($customer->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Customer Name</label>
                <input type="text" name="customer_name" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="Optional">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Sale Date</label>
                <input type="date" name="sale_date" value="<?php echo e(date('Y-m-d')); ?>" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" required>
            </div>
        </div>

        
        <div class="mb-5">
            <h3 class="font-semibold text-slate-800 mb-2">Products</h3>

            <div class="overflow-x-auto border border-slate-200 rounded-lg">
                <table class="min-w-full text-sm" id="items-table">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Product</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-slate-500 w-20">Qty</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-slate-500 w-28">Price</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-slate-500 w-28">Amount</th>
                            <th class="px-3 py-2 w-8"></th>
                        </tr>
                    </thead>
                    <tbody id="items-body">
                        <tr class="border-t border-slate-100">
                            <td class="px-3 py-2">
                                <input type="text" name="product_names[]" list="product-list" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm" placeholder="Type product name..." required>
                                <datalist id="product-list">
                                    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($product->name); ?>" data-price="<?php echo e($product->selling_price ?? 0); ?>">
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </datalist>
                            </td>
                            <td class="px-3 py-2"><input type="number" name="qtys[]" value="1" min="0.01" step="0.01" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm text-right qty" oninput="calcRow(this)"></td>
                            <td class="px-3 py-2"><input type="number" name="prices[]" value="0" step="0.01" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm text-right price" oninput="calcRow(this)"></td>
                            <td class="px-3 py-2 text-right amount font-medium">0</td>
                            <td class="px-3 py-2 text-center"><button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700 text-sm">✕</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <button type="button" onclick="addRow()" class="mt-2 text-sm text-blue-600 font-medium hover:text-blue-800">+ Add Product</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Total</label>
                <input type="text" id="total-display" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-slate-50 font-semibold" readonly value="0">
                <input type="hidden" name="total" id="total-input" value="0">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Paid</label>
                <input type="number" name="paid" value="0" step="0.01" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select name="status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    <option value="partial">Partial</option>
                    <option value="paid">Paid</option>
                    <option value="unpaid">Unpaid</option>
                </select>
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn-primary-soft">Save Sale</button>
            <a href="<?php echo e(route('sales.index')); ?>" class="px-4 py-2 text-sm text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>

<script>
function addRow() {
    const tbody = document.getElementById('items-body');
    const firstRow = tbody.rows[0];
    const newRow = firstRow.cloneNode(true);
    newRow.querySelectorAll('input').forEach(input => {
        if (input.name.includes('qty')) input.value = 1;
        else if (input.name.includes('price')) input.value = 0;
        else input.value = '';
    });
    newRow.querySelector('.amount').textContent = '0';
    tbody.appendChild(newRow);
}

function removeRow(btn) {
    const tbody = document.getElementById('items-body');
    if (tbody.rows.length > 1) {
        btn.closest('tr').remove();
        updateTotal();
    }
}

function calcRow(input) {
    const row = input.closest('tr');
    const qty = parseFloat(row.querySelector('.qty').value) || 0;
    const price = parseFloat(row.querySelector('.price').value) || 0;
    const amount = qty * price;
    row.querySelector('.amount').textContent = amount.toLocaleString();
    updateTotal();
}

function updateTotal() {
    let total = 0;
    document.querySelectorAll('#items-body tr').forEach(row => {
        const qty = parseFloat(row.querySelector('.qty').value) || 0;
        const price = parseFloat(row.querySelector('.price').value) || 0;
        total += qty * price;
    });
    document.getElementById('total-display').value = total.toLocaleString();
    document.getElementById('total-input').value = total;
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Admin\Desktop\Patu\deo-traders\resources\views/sales/create.blade.php ENDPATH**/ ?>