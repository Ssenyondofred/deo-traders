<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductionRequest;
use App\Models\ProductionBatch;
use App\Models\StockBalance;
use App\Services\InventoryService;
use App\Services\ProductionService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ProductionController extends Controller
{
    public function __construct(
        private ProductionService $production,
        private InventoryService $inventory,
    ) {
    }

    public function index(Request $request)
    {
        $query = ProductionBatch::with('creator')
            ->orderByDesc('production_date')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if (! auth()->user()->isAdmin()) {
            // $query->where('created_by', auth()->id());
        }

        $batches = $query->paginate(15)->withQueryString();

        return view('production.index', compact('batches'));
    }

    public function create()
    {
        $stock = $this->currentStockSummary();

        return view('production.form', [
            'batch' => null,
            'stock' => $stock,
        ]);
    }

    public function store(ProductionRequest $request)
    {
        try {
            $batch = $this->production->create($request->validated());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $message = 'Batch produksi disimpan sebagai draft.';

        if ($request->boolean('submit_after_save')) {
            $this->production->submit($batch);
            $message = 'Batch disimpan dan diajukan untuk approval admin.';
        }

        return redirect()->route('production.show', $batch)->with('success', $message);
    }

    public function show(ProductionBatch $production)
    {
        $this->authorizeView($production);

        $production->load([
            'materials.product',
            'outputs.product',
            'creator',
            'approver',
            'journalEntry.lines.account',
        ]);

        return view('production.show', ['batch' => $production]);
    }

    public function edit(ProductionBatch $production)
    {
        $this->authorizeView($production);

        if (! $production->isEditable()) {
            return redirect()->route('production.show', $production)
                ->with('error', 'Batch tidak dapat diedit pada status ini.');
        }

        $production->load(['materials.product', 'outputs.product']);
        $stock = $this->currentStockSummary();

        return view('production.form', [
            'batch' => $production,
            'stock' => $stock,
        ]);
    }

    public function update(ProductionRequest $request, ProductionBatch $production)
    {
        $this->authorizeView($production);

        try {
            $this->production->update($production, $request->validated());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($request->boolean('submit_after_save')) {
            $this->production->submit($production);
        }

        return redirect()->route('production.show', $production)->with('success', 'Batch produksi diperbarui.');
    }

    public function submit(ProductionBatch $production)
    {
        $this->authorizeView($production);

        try {
            $this->production->submit($production);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Batch diajukan ke admin untuk approval.');
    }

    public function approve(ProductionBatch $production)
    {
        if (! auth()->user()->hasPermission('production.approve')) {
            abort(403);
        }

        try {
            $this->production->approve($production);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Produksi disetujui. Stok dan jurnal telah diposting.');
    }

    public function reject(Request $request, ProductionBatch $production)
    {
        if (! auth()->user()->hasPermission('production.approve')) {
            abort(403);
        }

        $request->validate(['rejection_reason' => ['required', 'string', 'max:500']]);

        try {
            $this->production->reject($production, $request->rejection_reason);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Batch produksi ditolak.');
    }

    private function authorizeView(ProductionBatch $batch): void
    {
        // if (! auth()->user()->isAdmin() && $batch->created_by !== auth()->id()) {
        //     abort(403);
        // }
    }

    private function currentStockSummary(): array
    {
        $warehouse = $this->inventory->defaultWarehouse();
        $codes = ['RM-CORN' => 'Jagung Mentah', 'RM-COAL' => 'Batubara', 'FG-CORN' => 'Jagung Kering'];

        $summary = [];
        foreach ($codes as $code => $label) {
            $product = \App\Models\Product::where('code', $code)->first();
            $balance = $product
                ? StockBalance::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->first()
                : null;
            $summary[$code] = [
                'label' => $label,
                'qty' => (float) ($balance?->qty_on_hand ?? 0),
                'avg_cost' => (float) ($balance?->avg_cost ?? 0),
                'unit' => $product?->unit ?? '',
            ];
        }

        return $summary;
    }
}
