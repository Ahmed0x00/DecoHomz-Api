<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\Setting;
use Illuminate\Http\Request;

class AffiliateController extends Controller
{
    public function index(Request $request)
    {
        $query = Affiliate::with('user')->withCount(['referrals as pending_referrals' => function ($query) {
            $query->whereIn('commission_status', ['pending', 'holding']);
        }]);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('referral_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
        }

        $affiliates = $query->latest()->paginate(20);

        return response()->json($affiliates);
    }

    public function show(Affiliate $affiliate)
    {
        $affiliate->load('user');
        
        $affiliateService = app(\App\Services\AffiliateService::class);
        $balances = $affiliateService->getBalances($affiliate);

        $referrals = $affiliate->referrals()
            ->with(['order:id,order_number', 'referredUser:id,name'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'affiliate' => $affiliate,
            'balances' => $balances,
            'referrals' => $referrals
        ]);
    }

    public function toggleStatus(Affiliate $affiliate)
    {
        $affiliate->update(['is_active' => !$affiliate->is_active]);
        
        return response()->json([
            'message' => 'Affiliate status updated successfully.',
            'affiliate' => $affiliate->fresh()
        ]);
    }

    public function settings()
    {
        $settings = [
            'affiliate_program_active' => Setting::getValue('affiliate_program_active', '0'),
            'affiliate_discount_percent' => Setting::getValue('affiliate_discount_percent', '3'),
            'affiliate_discount_cap' => Setting::getValue('affiliate_discount_cap', '500'),
            'affiliate_commission_percent' => Setting::getValue('affiliate_commission_percent', '3'),
            'affiliate_commission_cap' => Setting::getValue('affiliate_commission_cap', '500'),
            'affiliate_min_order' => Setting::getValue('affiliate_min_order', '2000'),
            'affiliate_hold_days' => Setting::getValue('affiliate_hold_days', '14'),
            'affiliate_include_vendor' => Setting::getValue('affiliate_include_vendor', '0'),
        ];

        return response()->json($settings);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'affiliate_program_active' => 'required|boolean',
            'affiliate_discount_percent' => 'required|numeric|min:0|max:100',
            'affiliate_discount_cap' => 'required|numeric|min:0',
            'affiliate_commission_percent' => 'required|numeric|min:0|max:100',
            'affiliate_commission_cap' => 'required|numeric|min:0',
            'affiliate_min_order' => 'required|numeric|min:0',
            'affiliate_hold_days' => 'required|integer|min:0',
            'affiliate_include_vendor' => 'required|boolean',
        ]);

        foreach ($validated as $key => $value) {
            $val = is_bool($value) ? ($value ? '1' : '0') : (string) $value;
            Setting::setValue($key, $val);
        }

        return response()->json(['message' => 'Affiliate program settings updated.']);
    }
}
