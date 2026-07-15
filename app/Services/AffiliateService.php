<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Referral;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AffiliateService
{
    public function isProgramActive(): bool
    {
        return Setting::getValue('affiliate_program_active', '0') === '1';
    }

    public function joinProgram(User $user): Affiliate
    {
        if ($user->isAffiliate()) {
            return $user->affiliate;
        }

        return $user->affiliate()->create([
            'is_active' => true,
        ]);
    }

    public function checkFraud(Affiliate $affiliate, ?User $buyer, Request $request): array
    {
        $flags = [
            'self_referral' => false,
            'same_ip' => false,
        ];

        // Self referral (only possible if buyer is logged in)
        if ($buyer && $affiliate->user_id === $buyer->id) {
            $flags['self_referral'] = true;
        }

        // Same IP flag (logged for admin review, not blocking)
        $ipCheckEnabled = Setting::getValue('affiliate_ip_check', '1') === '1';
        $buyerIp = $request->ip();
        if ($ipCheckEnabled && $buyerIp) {
            $sameIpCount = Referral::where('affiliate_id', $affiliate->id)
                ->where('buyer_ip_address', $buyerIp)
                ->count();
            if ($sameIpCount > 0) {
                $flags['same_ip'] = true;
            }
        }

        return $flags;
    }

    public function applyCodeToCart(Cart $cart, string $code, ?User $buyer, Request $request): array
    {
        if (!$this->isProgramActive()) {
            return ['success' => false, 'message' => 'The affiliate program is currently inactive.'];
        }

        $affiliate = Affiliate::where('referral_code', $code)->where('is_active', true)->first();
        if (!$affiliate) {
            return ['success' => false, 'message' => 'Invalid or inactive affiliate code.'];
        }

        // Self-referral is the only hard block
        $flags = $this->checkFraud($affiliate, $buyer, $request);
        if ($flags['self_referral']) {
            return ['success' => false, 'message' => 'You cannot use your own referral code.'];
        }

        $subtotal = $cart->getSubtotalAttribute();
        $minOrder = (float) Setting::getValue('affiliate_min_order', 2000);
        
        if ($subtotal < $minOrder) {
            return ['success' => false, 'message' => "Order subtotal must be at least {$minOrder} EGP to use this code."];
        }

        // Mutually exclusive with coupon
        if ($cart->coupon_code) {
            return ['success' => false, 'message' => 'Cannot combine affiliate code with coupon code.'];
        }

        $eligibleSubtotal = 0;
        $includeVendorProducts = Setting::getValue('affiliate_include_vendor', '0') === '1';

        foreach ($cart->items as $item) {
            if (!$includeVendorProducts && $item->product && $item->product->vendor_id !== null) {
                continue; // Skip vendor products
            }
            $eligibleSubtotal += ($item->price * $item->quantity);
        }

        if ($eligibleSubtotal <= 0) {
            return ['success' => false, 'message' => 'No eligible products in cart for affiliate discount.'];
        }

        $discount = $this->calculateDiscount($eligibleSubtotal);

        $cart->update([
            'affiliate_code' => $code,
            'affiliate_discount' => $discount,
            'discount' => 0, // Ensure coupon discount is cleared
            'coupon_code' => null,
        ]);

        return ['success' => true, 'message' => 'Affiliate code applied successfully.'];
    }

    public function removeCodeFromCart(Cart $cart): void
    {
        $cart->update([
            'affiliate_code' => null,
            'affiliate_discount' => 0,
        ]);
    }

    public function calculateDiscount(float $eligibleSubtotal): float
    {
        $percent = (float) Setting::getValue('affiliate_discount_percent', 3);
        $cap = (float) Setting::getValue('affiliate_discount_cap', 500);

        $discount = ($eligibleSubtotal * $percent) / 100;
        return min($discount, $cap);
    }

    public function calculateCommission(float $eligibleSubtotal): float
    {
        $percent = (float) Setting::getValue('affiliate_commission_percent', 3);
        $cap = (float) Setting::getValue('affiliate_commission_cap', 500);

        $commission = ($eligibleSubtotal * $percent) / 100;
        return min($commission, $cap);
    }

    public function createReferral(Order $order, string $code, ?User $buyer, ?string $ipAddress = null): ?Referral
    {
        $affiliate = Affiliate::where('referral_code', $code)->first();
        if (!$affiliate) {
            \Log::info("createReferral failed: affiliate not found for code {$code}");
            return null;
        }

        $eligibleSubtotal = 0;
        $includeVendorProducts = Setting::getValue('affiliate_include_vendor', '0') === '1';

        foreach ($order->items as $item) {
            if (!$includeVendorProducts && $item->product && $item->product->vendor_id !== null) {
                continue;
            }
            $eligibleSubtotal += ($item->price * $item->quantity);
        }

        $commissionAmount = $this->calculateCommission($eligibleSubtotal);
        if ($commissionAmount <= 0) {
            \Log::info("createReferral failed: commission amount <= 0. EligibleSubtotal: {$eligibleSubtotal}");
            return null;
        }

        $ipCheckEnabled = Setting::getValue('affiliate_ip_check', '1') === '1';

        $fraudFlags = [
            'self_referral' => $buyer && $affiliate->user_id === $buyer->id,
            'same_ip' => ($ipCheckEnabled && $ipAddress) ? Referral::where('affiliate_id', $affiliate->id)->where('buyer_ip_address', $ipAddress)->exists() : false,
            'is_guest' => $buyer === null,
        ];

        $referral = Referral::create([
            'affiliate_id' => $affiliate->id,
            'order_id' => $order->id,
            'referred_user_id' => $buyer?->id,
            'order_subtotal' => $order->subtotal,
            'discount_amount' => $order->affiliate_discount,
            'commission_amount' => $commissionAmount,
            'commission_status' => Referral::STATUS_PENDING,
            'buyer_ip_address' => $ipAddress,
            'fraud_flags' => $fraudFlags,
        ]);

        if ($referral) {
            $order->update(['referral_id' => $referral->id]);
        }

        return $referral;
    }

    public function markOrderDelivered(Order $order): void
    {
        if ($order->referral) {
            $holdDays = (int) Setting::getValue('affiliate_hold_days', 14);
            $order->referral->update([
                'commission_status' => Referral::STATUS_HOLDING,
                'hold_started_at' => now(),
                'hold_expires_at' => now()->addDays($holdDays),
            ]);
        }
    }

    public function revokeCommission(Referral $referral, string $reason): void
    {
        if ($referral->commission_status === Referral::STATUS_PAID) {
            $referral->update([
                'commission_status' => Referral::STATUS_CLAWBACK,
                'revoke_reason' => $reason
            ]);
            $referral->affiliate->decrement('total_earnings', $referral->commission_amount);
        } else {
            $referral->update([
                'commission_status' => Referral::STATUS_REVOKED,
                'revoke_reason' => $reason
            ]);
        }
    }

    public function processApprovals(): int
    {
        $approvedCount = 0;
        
        Referral::where('commission_status', Referral::STATUS_HOLDING)
            ->whereNotNull('hold_expires_at')
            ->where('hold_expires_at', '<=', now())
            ->chunkById(100, function ($referrals) use (&$approvedCount) {
                foreach ($referrals as $referral) {
                    $referral->update([
                        'commission_status' => Referral::STATUS_APPROVED,
                        'approved_at' => now(),
                    ]);
                    $approvedCount++;
                }
            });

        return $approvedCount;
    }

    public function processPayout(Affiliate $affiliate, array $referralIds, string $reference): array
    {
        $referrals = $affiliate->referrals()
            ->whereIn('id', $referralIds)
            ->where('commission_status', Referral::STATUS_APPROVED)
            ->get();

        if ($referrals->isEmpty()) {
            return ['success' => false, 'message' => 'No approved referrals found for payout.'];
        }

        $totalPayout = $referrals->sum('commission_amount');

        DB::transaction(function () use ($affiliate, $referrals, $totalPayout, $reference) {
            foreach ($referrals as $referral) {
                $referral->update([
                    'commission_status' => Referral::STATUS_PAID,
                    'paid_at' => now(),
                    'payout_reference' => $reference,
                ]);
            }

            $affiliate->increment('total_earnings', $totalPayout);
        });

        return ['success' => true, 'total_paid' => $totalPayout];
    }

    public function getBalances(Affiliate $affiliate): array
    {
        $clawbacks = $affiliate->referrals()->where('commission_status', Referral::STATUS_CLAWBACK)->sum('commission_amount');
        
        $pending = $affiliate->referrals()->whereIn('commission_status', [Referral::STATUS_PENDING, Referral::STATUS_HOLDING])->sum('commission_amount');
        $approved = $affiliate->referrals()->where('commission_status', Referral::STATUS_APPROVED)->sum('commission_amount');
        
        $effectiveApproved = max(0, $approved - $clawbacks);

        return [
            'pending' => round($pending, 2),
            'approved' => round($effectiveApproved, 2),
            'paid' => round($affiliate->total_earnings, 2),
            'total_referrals' => $affiliate->referrals()->whereNotIn('commission_status', [Referral::STATUS_REVOKED])->count(),
        ];
    }
}
