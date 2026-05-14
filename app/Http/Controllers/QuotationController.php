<?php

namespace App\Http\Controllers;

use App\Models\CostingSheet;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuotationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Quotations/Index', [
            'quotations' => Quotation::with('costingSheet')
                ->latest()
                ->get()
                ->map(fn($q) => [
                    'id' => $q->id,
                    'reference' => $q->reference,
                    'project_name' => $q->project_name,
                    'client_name' => $q->client_name,
                    'date' => $q->date->format('Y-m-d'),
                    'valid_until' => $q->valid_until?->format('Y-m-d'),
                    'status' => $q->status,
                    'total' => $q->total,
                    'costing_reference' => $q->costingSheet->reference,
                ]),
        ]);
    }

    public function fromCosting(CostingSheet $costingSheet)
    {
        if ($costingSheet->status === 'converted') {
            return redirect()->route('quotations.show', $costingSheet->quotation);
        }

        $costingSheet->load('items');

        $quotation = Quotation::create([
            'costing_sheet_id' => $costingSheet->id,
            'project_name' => $costingSheet->project_name,
            'client_name' => $costingSheet->client_name,
            'client_email' => $costingSheet->client_email,
            'client_phone' => $costingSheet->client_phone,
            'prepared_by' => $costingSheet->prepared_by,
            'date' => now(),
            'valid_until' => now()->addDays(30),
            'notes' => $costingSheet->notes,
            'overhead_percent' => $costingSheet->overhead_percent,
            'profit_margin_percent' => $costingSheet->profit_margin_percent,
            'discount_percent' => $costingSheet->discount_percent,
            'tax_percent' => $costingSheet->tax_percent,
        ]);

        $margin = 1 + ($costingSheet->overhead_percent / 100) + ($costingSheet->profit_margin_percent / 100);

        foreach ($costingSheet->items as $i => $item) {
            $quotation->items()->create([
                'type' => $item->type,
                'description' => $item->description,
                'unit' => $item->unit,
                'quantity' => $item->quantity,
                'unit_cost' => $item->unit_cost,
                'selling_price' => round($item->unit_cost * $margin, 2),
                'sort_order' => $i,
            ]);
        }

        $costingSheet->update(['status' => 'converted']);

        return redirect()->route('quotations.show', $quotation)->with('success', 'Quotation created from costing sheet.');
    }

    public function show(Quotation $quotation): Response
    {
        $quotation->load(['items', 'costingSheet']);

        return Inertia::render('Quotations/Show', [
            'quotation' => [
                ...$quotation->toArray(),
                'items' => $quotation->items->map(fn($i) => [
                    ...$i->toArray(),
                    'total_cost' => $i->total_cost,
                    'total_selling' => $i->total_selling,
                ]),
                'raw_cost' => $quotation->raw_cost,
                'subtotal' => $quotation->subtotal,
                'discount_amount' => $quotation->discount_amount,
                'tax_amount' => $quotation->tax_amount,
                'total' => $quotation->total,
                'costing_reference' => $quotation->costingSheet->reference,
            ],
        ]);
    }

    public function edit(Quotation $quotation): Response
    {
        $quotation->load('items');

        return Inertia::render('Quotations/Form', [
            'quotation' => [
                ...$quotation->toArray(),
                'items' => $quotation->items,
            ],
        ]);
    }

    public function update(Request $request, Quotation $quotation)
    {
        $data = $request->validate([
            'project_name' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'client_phone' => 'nullable|string|max:50',
            'prepared_by' => 'nullable|string|max:255',
            'date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'overhead_percent' => 'nullable|numeric|min:0|max:100',
            'profit_margin_percent' => 'nullable|numeric|min:0|max:100',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:draft,sent,accepted,rejected',
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:material,labor',
            'items.*.description' => 'required|string|max:255',
            'items.*.unit' => 'required|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.selling_price' => 'required|numeric|min:0',
        ]);

        $quotation->update($data);
        $quotation->items()->delete();

        foreach ($data['items'] as $i => $item) {
            $quotation->items()->create([...$item, 'sort_order' => $i]);
        }

        return redirect()->route('quotations.show', $quotation)->with('success', 'Quotation updated.');
    }

    public function updateStatus(Request $request, Quotation $quotation)
    {
        $data = $request->validate([
            'status' => 'required|in:draft,sent,accepted,rejected',
        ]);

        $quotation->update($data);

        return back()->with('success', 'Quotation status updated.');
    }

    public function destroy(Quotation $quotation)
    {
        $costingSheet = $quotation->costingSheet;
        $quotation->delete();
        $costingSheet->update(['status' => 'finalized']);

        return redirect()->route('quotations.index')->with('success', 'Quotation deleted.');
    }
}
