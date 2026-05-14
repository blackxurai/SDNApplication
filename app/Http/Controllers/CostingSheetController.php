<?php

namespace App\Http\Controllers;

use App\Models\CostingSheet;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CostingSheetController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('CostingSheets/Index', [
            'sheets' => CostingSheet::with('quotation')
                ->latest()
                ->get()
                ->map(fn($s) => [
                    'id' => $s->id,
                    'reference' => $s->reference,
                    'project_name' => $s->project_name,
                    'client_name' => $s->client_name,
                    'date' => $s->date->format('Y-m-d'),
                    'status' => $s->status,
                    'total' => $s->total,
                    'has_quotation' => $s->quotation !== null,
                    'quotation_id' => $s->quotation?->id,
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('CostingSheets/Form', ['sheet' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_name' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'client_phone' => 'nullable|string|max:50',
            'prepared_by' => 'nullable|string|max:255',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'overhead_percent' => 'nullable|numeric|min:0|max:100',
            'profit_margin_percent' => 'nullable|numeric|min:0|max:100',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:material,labor',
            'items.*.description' => 'required|string|max:255',
            'items.*.unit' => 'required|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $sheet = CostingSheet::create($data);

        foreach ($data['items'] as $i => $item) {
            $sheet->items()->create([...$item, 'sort_order' => $i]);
        }

        return redirect()->route('costing-sheets.show', $sheet)->with('success', 'Costing sheet created.');
    }

    public function show(CostingSheet $costingSheet): Response
    {
        $costingSheet->load(['items', 'quotation']);

        return Inertia::render('CostingSheets/Show', [
            'sheet' => [
                ...$costingSheet->toArray(),
                'items' => $costingSheet->items->map(fn($i) => [
                    ...$i->toArray(),
                    'total' => $i->total,
                ]),
                'raw_cost' => $costingSheet->raw_cost,
                'overhead_amount' => $costingSheet->overhead_amount,
                'profit_amount' => $costingSheet->profit_amount,
                'subtotal' => $costingSheet->subtotal,
                'discount_amount' => $costingSheet->discount_amount,
                'tax_amount' => $costingSheet->tax_amount,
                'total' => $costingSheet->total,
                'quotation' => $costingSheet->quotation ? [
                    'id' => $costingSheet->quotation->id,
                    'reference' => $costingSheet->quotation->reference,
                    'status' => $costingSheet->quotation->status,
                ] : null,
            ],
        ]);
    }

    public function edit(CostingSheet $costingSheet): Response
    {
        $costingSheet->load('items');

        return Inertia::render('CostingSheets/Form', [
            'sheet' => [
                ...$costingSheet->toArray(),
                'items' => $costingSheet->items,
            ],
        ]);
    }

    public function update(Request $request, CostingSheet $costingSheet)
    {
        if ($costingSheet->status === 'converted') {
            return back()->withErrors(['error' => 'Cannot edit a converted costing sheet.']);
        }

        $data = $request->validate([
            'project_name' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'client_phone' => 'nullable|string|max:50',
            'prepared_by' => 'nullable|string|max:255',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'overhead_percent' => 'nullable|numeric|min:0|max:100',
            'profit_margin_percent' => 'nullable|numeric|min:0|max:100',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:material,labor',
            'items.*.description' => 'required|string|max:255',
            'items.*.unit' => 'required|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $costingSheet->update($data);
        $costingSheet->items()->delete();

        foreach ($data['items'] as $i => $item) {
            $costingSheet->items()->create([...$item, 'sort_order' => $i]);
        }

        return redirect()->route('costing-sheets.show', $costingSheet)->with('success', 'Costing sheet updated.');
    }

    public function destroy(CostingSheet $costingSheet)
    {
        if ($costingSheet->status === 'converted') {
            return back()->withErrors(['error' => 'Cannot delete a converted costing sheet.']);
        }

        $costingSheet->delete();

        return redirect()->route('costing-sheets.index')->with('success', 'Costing sheet deleted.');
    }

    public function finalize(CostingSheet $costingSheet)
    {
        $costingSheet->update(['status' => 'finalized']);

        return back()->with('success', 'Costing sheet finalized.');
    }
}
