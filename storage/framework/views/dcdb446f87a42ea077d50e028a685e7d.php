<?php $__env->startSection('title', 'Reports'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-900">Reports</h2>
    <p class="text-sm text-slate-500 mt-1">Sales and purchase summary</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="panel p-5">
        <h3 class="font-semibold text-slate-800 mb-3">Sales Summary</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between"><span>Today</span><span class="font-medium">UGX <?php echo e(number_format($todaySales, 0)); ?></span></div>
            <div class="flex justify-between"><span>This Month</span><span class="font-medium">UGX <?php echo e(number_format($monthSales, 0)); ?></span></div>
            <div class="flex justify-between"><span>Total Outstanding</span><span class="font-medium text-red-600">UGX <?php echo e(number_format($totalDebt, 0)); ?></span></div>
        </div>
    </div>
    <div class="panel p-5">
        <h3 class="font-semibold text-slate-800 mb-3">Purchases Summary</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between"><span>Today</span><span class="font-medium">UGX <?php echo e(number_format($todayPurchases, 0)); ?></span></div>
            <div class="flex justify-between"><span>This Month</span><span class="font-medium">UGX <?php echo e(number_format($monthPurchases, 0)); ?></span></div>
            <div class="flex justify-between"><span>Total Creditors</span><span class="font-medium text-amber-600">UGX <?php echo e(number_format($totalCreditors, 0)); ?></span></div>
        </div>
    </div>
</div>


<div class="panel p-5 mb-6">
    <h3 class="font-semibold text-slate-800 mb-3">Sales Last 7 Days</h3>
    <canvas id="salesChart" height="80"></canvas>
</div>

<div class="panel p-5">
    <h3 class="font-semibold text-slate-800 mb-3">Top Debtors</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Invoice</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Customer</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $debtors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="border-b border-slate-100">
                        <td class="px-3 py-2"><a href="<?php echo e(route('sales.show', $d)); ?>" class="text-blue-600"><?php echo e($d->invoice_number); ?></a></td>
                        <td class="px-3 py-2"><?php echo e($d->customer_name ?: ($d->customer->name ?? 'Walk-in')); ?></td>
                        <td class="px-3 py-2 text-right font-semibold text-red-600"><?php echo e(number_format($d->balance, 0)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="3" class="px-3 py-4 text-center text-slate-500">No outstanding balances.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chartLabels); ?>,
        datasets: [{
            label: 'Sales (UGX)',
            data: <?php echo json_encode($chartData); ?>,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Admin\Desktop\Patu\deo-traders\resources\views/reports/index.blade.php ENDPATH**/ ?>