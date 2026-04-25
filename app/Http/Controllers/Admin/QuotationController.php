<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotationController extends Controller
{
    public function list()
    {
        try {
            $quotations = Quotation::all();
            return view('admin.pages.quotations.list', compact('quotations'));
        } catch (\Throwable $th) {
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Failed to fetch quotations. ' . $th->getMessage(),
            ]);
        }
    }
    public function create()
    {
        
        return view('admin.pages.quotations.create');
    }
    public function store(Request $request)
    {
        
        $validated = $request->validate([
            'consignee_name'      => 'required|string|max:255',
            'consignee_address'   => 'required|string|max:255',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'invoice_no'          => 'required|string|max:255',
            'invoice_date'        => 'required|date',
            'fi_no'               => 'required|string|max:255',
            'destination'         => 'required|string|max:255',
            'shipment_mode'         => 'required|string|max:100',
            'address'       => 'required|string|max:255',
            'payment_term'        => 'required|string|max:255',
            'freight_term'        => 'required|string|max:255',
            'hs_code'             => 'required|string|max:255',
            'brand'               => 'required|string|max:255',
            'rate_per_ton'        => 'required|numeric|min:0',
            'bank_account'        => 'nullable|string|max:255',
            'currency'            => 'required|string|max:255',
            'iban'                => 'nullable|string|max:255',
            'swift_code'          => 'nullable|string|max:255',
            'company_name'        => 'nullable|string|max:255',
            'bank_name'           => 'nullable|string|max:255',
            'total_bags'          => 'required|integer|min:0',
            'total_net_weight'    => 'required|numeric|min:0',
            'total_gross_weight'  => 'required|numeric|min:0',
            'total_value_usd'     => 'required|numeric|min:0',
            'items'               => 'required|array|min:1',
            'items.*.no_of_bags'  => 'required|integer|min:0',
            'items.*.pack_details' => 'required|string|max:255',
            'items.*.net_weight'  => 'required|numeric|min:0',
            'items.*.gross_weight' => 'required|numeric|min:0',
            'items.*.price'       => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $quotation = Quotation::create([
                'uuid'               => Str::uuid(),
                'consignee_name'     => $validated['consignee_name'],
                'consignee_address'  => $validated['consignee_address'],
                'percentage' => $validated['percentage'] ?? null,
                'invoice_no'         => $validated['invoice_no'],
                'invoice_date'       => $validated['invoice_date'],
                'fi_no'              => $validated['fi_no'],
                'destination'        => $validated['destination'],
                'address'            => $validated['address'],
                'shipment_mode'      => $validated['shipment_mode'],
                'currency'           => $validated['currency'],
                'payment_term'       => $validated['payment_term'],
                'freight_term'       => $validated['freight_term'],
                'hs_code'            => $validated['hs_code'],
                'description'        => $validated['brand'], 
                'rate_per_ton'       => $validated['rate_per_ton'],
                'total_value'        => $validated['total_value_usd'],
                'container_details'  => json_encode($validated['items']),
                'bank_account'       => $validated['bank_account'] ?? '',
                'iban'               => $validated['iban'] ?? '',
                'swift_code'         => $validated['swift_code'] ?? '',
                'company_name'       => $validated['company_name'] ?? '',
                'bank_name'          => $validated['bank_name'] ?? '',
                'total_bags'         => $validated['total_bags'],
                'total_net_weight'   => $validated['total_net_weight'],
                'total_gross_weight' => $validated['total_gross_weight'],
                'total_value_usd'    => $validated['total_value_usd'],
            ]);

            foreach ($validated['items'] as $item) {
                QuotationItem::create([
                    'uuid'           => Str::uuid(),
                    'container_no'   => '', 
                    'no_of_bags'     => $item['no_of_bags'],
                    'package_details' => $item['pack_details'],
                    'kg_in_bag'      => '',
                    'net_weight'     => $item['net_weight'],
                    'gross_weight'   => $item['gross_weight'],
                    'total_value'    => $item['price'],
                    'quotation_id'   => $quotation->id,
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Quotation created successfully.',
                'redirect' => route('quotations.list')
            ]);
        } catch (\Throwable $th) {
            dd($th->getMessage());
            Log::error('Failed to create quotation. ' . $th->getMessage());
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create quotation. ' . $th->getMessage(),
            ], 500);
        }
    }
    public function edit($uuid)
{
    $quotation = Quotation::where('uuid', $uuid)->firstOrFail();
    $quotation->items = QuotationItem::where('quotation_id', $quotation->id)->get();

    return view('admin.pages.quotations.edit', compact('quotation'));
}

public function update(Request $request, $uuid)
{
    $quotation = Quotation::where('uuid', $uuid)->firstOrFail();

    $validated = $request->validate([
        'consignee_name'      => 'required|string|max:255',
        'consignee_address'   => 'required|string|max:255',
        'percentage'          => 'nullable|numeric|min:0|max:100',
        'invoice_no'          => 'required|string|max:255',
        'invoice_date'        => 'required|date',
        'fi_no'               => 'required|string|max:255',
        'destination'         => 'required|string|max:255',
        'shipment_mode'       => 'required|string|max:100',
        'address'             => 'required|string|max:255',
        'payment_term'        => 'required|string|max:255',
        'freight_term'        => 'required|string|max:255',
        'hs_code'             => 'required|string|max:255',
        'brand'               => 'required|string|max:255',
        'rate_per_ton'        => 'required|numeric|min:0',
        'bank_account'        => 'nullable|string|max:255',
        'currency'            => 'required|string|max:255',
        'iban'                => 'nullable|string|max:255',
        'swift_code'          => 'nullable|string|max:255',
        'company_name'        => 'nullable|string|max:255',
        'bank_name'           => 'nullable|string|max:255',
        'total_bags'          => 'required|integer|min:0',
        'total_net_weight'    => 'required|numeric|min:0',
        'total_gross_weight'  => 'required|numeric|min:0',
        'total_value_usd'     => 'required|numeric|min:0',
        'items'               => 'required|array|min:1',
        'items.*.uuid'        => 'nullable|string|max:36', 
        'items.*.no_of_bags'  => 'required|integer|min:0',
        'items.*.pack_details' => 'required|string|max:255',
        'items.*.net_weight'  => 'required|numeric|min:0',
        'items.*.gross_weight' => 'required|numeric|min:0',
        'items.*.price'       => 'required|numeric|min:0',
    ]);

    DB::beginTransaction();
    try {
        $quotation->update([
            'consignee_name'     => $validated['consignee_name'],
            'consignee_address'  => $validated['consignee_address'],
            'percentage'         => $validated['percentage'] ?? null,
            'invoice_no'         => $validated['invoice_no'],
            'invoice_date'       => $validated['invoice_date'],
            'fi_no'              => $validated['fi_no'],
            'destination'        => $validated['destination'],
            'address'            => $validated['address'],
            'shipment_mode'      => $validated['shipment_mode'],
            'currency'           => $validated['currency'],
            'payment_term'       => $validated['payment_term'],
            'freight_term'       => $validated['freight_term'],
            'hs_code'            => $validated['hs_code'],
            'description'        => $validated['brand'], 
            'rate_per_ton'       => $validated['rate_per_ton'],
            'total_value'        => $validated['total_value_usd'], 
            'bank_account'       => $validated['bank_account'] ?? '',
            'iban'               => $validated['iban'] ?? '',
            'swift_code'         => $validated['swift_code'] ?? '',
            'company_name'       => $validated['company_name'] ?? '',
            'bank_name'          => $validated['bank_name'] ?? '',
            'total_bags'         => $validated['total_bags'],
            'total_net_weight'   => $validated['total_net_weight'],
            'total_gross_weight' => $validated['total_gross_weight'],
            'total_value_usd'    => $validated['total_value_usd'],
        ]);

        
        $existingItemUuids = $quotation->items()->pluck('uuid')->toArray();
        $updatedItemUuids = [];

        foreach ($validated['items'] as $itemData) {
            if (isset($itemData['uuid']) && $itemData['uuid']) {
                
                QuotationItem::where('uuid', $itemData['uuid'])->update([
                    'no_of_bags'      => $itemData['no_of_bags'],
                    'package_details' => $itemData['pack_details'],
                    'net_weight'      => $itemData['net_weight'],
                    'gross_weight'    => $itemData['gross_weight'],
                    'total_value'     => $itemData['price'],
                ]);
                $updatedItemUuids[] = $itemData['uuid'];
            } else {
                
                QuotationItem::create([
                    'uuid'            => Str::uuid(),
                    'container_no'    => '',
                    'no_of_bags'      => $itemData['no_of_bags'],
                    'package_details' => $itemData['pack_details'],
                    'kg_in_bag'       => '',
                    'net_weight'      => $itemData['net_weight'],
                    'gross_weight'    => $itemData['gross_weight'],
                    'total_value'     => $itemData['price'],
                    'quotation_id'    => $quotation->id,
                ]);
            }
        }

        
        $itemsToDelete = array_diff($existingItemUuids, $updatedItemUuids);
        if (!empty($itemsToDelete)) {
            QuotationItem::whereIn('uuid', $itemsToDelete)->delete();
        }

        DB::commit();
        return response()->json([
            'status' => true,
            'message' => 'Quotation updated successfully.',
            'redirect' => route('quotations.list')
        ]);
    } catch (\Throwable $th) {
        Log::error('Failed to update quotation. ' . $th->getMessage());
        DB::rollBack();
        return response()->json([
            'status' => false,
            'message' => 'Failed to update quotation. ' . $th->getMessage(),
        ], 500);
    }
}
    public function view($uuid)
    {
        
        try {
            $quotation = Quotation::where('uuid', $uuid)->first();
            if (!$quotation) {
                return redirect()->back()->with([
                    'status' => false,
                    'message' => 'Quotation not found.',
                ]);
            }
            return view('admin.pages.quotations.view', compact('quotation'));
        } catch (\Throwable $th) {
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Failed to fetch quotation. ' . $th->getMessage(),
            ]);
        }
    }
    public function delete($uuid)
        {
            try {
                $quotation = Quotation::where('uuid', $uuid)->firstOrFail();
                $quotation->items()->delete();
                $quotation->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Quotation deleted successfully!'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to delete quotation. ' . $e->getMessage()
                ]);
            }
        }

    public function downloadPdf(string $uuid)
    {
        $quotation = Quotation::where('uuid', $uuid)->firstOrFail();
        $items = $quotation->items ?? [];
        $pdf = Pdf::loadView('admin.pages.quotations.pdf', compact('quotation', 'items'));
        return $pdf->download('quotation-' . $quotation->uuid . '.pdf');
    }
}
