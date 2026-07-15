<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AffiliateService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AffiliateController extends Controller
{
    protected $affiliateService;

    public function __construct(AffiliateService $affiliateService)
    {
        $this->affiliateService = $affiliateService;
    }

    public function join(Request $request): JsonResponse
    {
        if (!$this->affiliateService->isProgramActive()) {
            return response()->json(['message' => 'The affiliate program is currently not active.'], 400);
        }

        $affiliate = $this->affiliateService->joinProgram($request->user());
        
        return response()->json([
            'message' => 'Successfully joined the affiliate program.',
            'affiliate' => $affiliate
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $isActive = $this->affiliateService->isProgramActive();
        $user = $request->user();
        $isAffiliate = $user && $user->isAffiliate();

        return response()->json([
            'active' => $isActive,
            'affiliate' => $isAffiliate ? $user->affiliate : null,
            'balances' => $isAffiliate ? $this->affiliateService->getBalances($user->affiliate) : null,
        ]);
    }

    public function updateBankDetails(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAffiliate()) {
            return response()->json(['message' => 'You are not part of the affiliate program.'], 403);
        }

        $validated = $request->validate([
            'bank_account_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:50',
            'bank_name' => 'required|string|max:100',
        ]);

        $user->affiliate->update($validated);

        return response()->json([
            'message' => 'Bank details updated successfully.',
            'affiliate' => $user->affiliate
        ]);
    }

    public function referrals(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAffiliate()) {
            return response()->json(['message' => 'You are not part of the affiliate program.'], 403);
        }

        $referrals = $user->affiliate->referrals()
            ->with(['order:id,order_number,created_at', 'referredUser:id,name'])
            ->latest()
            ->paginate(15);

        return response()->json($referrals);
    }
}
