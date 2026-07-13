<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\VendorTransaction;
use App\Services\VendorFinanceService;

class VendorFinanceController extends Controller
{
    public function index(Request $request)
    {
        // Simple overview: active vendors and their balances
        // For a large system, you'd aggregate this efficiently
        $vendors = Vendor::where('status', 'active')->paginate(20);
        $financeService = app(VendorFinanceService::class);

        $vendors->getCollection()->transform(function ($vendor) use ($financeService) {
            $vendor->balances = $financeService->getBalances($vendor);
            return $vendor;
        });

        return response()->json($vendors);
    }

    public function showVendorLedger($vendorId)
    {
        $vendor = Vendor::findOrFail($vendorId);
        $financeService = app(VendorFinanceService::class);
        
        return response()->json([
            'vendor' => $vendor,
            'balances' => $financeService->getBalances($vendor),
            'transactions' => $vendor->transactions()->latest()->paginate(20)
        ]);
    }

    public function processPayouts(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'required|string',
        ]);

        $vendor = Vendor::findOrFail($request->vendor_id);
        $financeService = app(VendorFinanceService::class);
        
        $transaction = $financeService->processPayout($vendor, $request->amount, $request->reference);

        if (!$transaction) {
            return response()->json(['message' => 'Amount exceeds available balance.'], 400);
        }

        return response()->json(['message' => 'Payout processed successfully.', 'transaction' => $transaction]);
    }
}
