<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\Referral;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function index(Request $request)
    {
        \DB::enableQueryLog();

        $query = Referral::with(['affiliate.user', 'order', 'referredUser']);

        if ($request->filled('status')) {
            $query->where('commission_status', $request->status);
        }

        $referrals = $query->latest()->paginate(20);

        \Log::info('Referrals API Query Log:', [
            'queries' => \DB::getQueryLog(),
            'total_items' => $referrals->total(),
            'items_count' => count($referrals->items()),
            'status_param' => $request->get('status'),
        ]);

        return response()->json($referrals);
    }

    public function processPayouts(Request $request)
    {
        $validated = $request->validate([
            'affiliate_id' => 'required|exists:affiliates,id',
            'referral_ids' => 'required|array',
            'referral_ids.*' => 'exists:referrals,id',
            'payout_reference' => 'required|string|max:100',
        ]);

        $affiliate = Affiliate::findOrFail($validated['affiliate_id']);
        
        $affiliateService = app(\App\Services\AffiliateService::class);
        $result = $affiliateService->processPayout($affiliate, $validated['referral_ids'], $validated['payout_reference']);

        if ($result['success']) {
            return response()->json(['message' => "Payout of {$result['total_paid']} EGP processed successfully."]);
        }

        return response()->json(['message' => $result['message']], 422);
    }

    public function show($id)
    {
        $referral = Referral::with(['affiliate.user', 'order.items.product', 'referredUser'])->findOrFail($id);
        return response()->json($referral);
    }

    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'referral_ids' => 'required|array',
            'referral_ids.*' => 'exists:referrals,id',
            'status' => 'required|in:approved,rejected,holding,pending,clawback',
            'reason' => 'nullable|string'
        ]);

        $referrals = Referral::whereIn('id', $validated['referral_ids'])->get();
        $updated = 0;

        foreach ($referrals as $referral) {
            // Only update if not already paid, unless we are clawing back
            if ($referral->commission_status === 'paid' && $validated['status'] !== 'clawback') {
                continue;
            }

            $referral->commission_status = $validated['status'];
            
            if ($validated['status'] === 'approved') {
                $referral->approved_at = now();
            } else if (in_array($validated['status'], ['rejected', 'clawback'])) {
                $referral->revoke_reason = $validated['reason'] ?? 'Manually ' . $validated['status'];
            }

            $referral->save();
            $updated++;
        }

        return response()->json(['message' => "Successfully updated status for {$updated} referrals."]);
    }
}
