<?php

namespace App\Services;

use App\Models\Vendor;
use App\Models\VendorTransaction;

class VendorFinanceService
{
    public function getBalances(Vendor $vendor): array
    {
        $saleCredits = $vendor->transactions()->where('type', 'sale_credit');

        $pending = (float) (clone $saleCredits)->where('status', 'pending')->sum('amount');
        $available = (float) (clone $saleCredits)->where('status', 'available')->sum('amount');
        $paidCredits = (float) (clone $saleCredits)->where('status', 'paid')->sum('amount');
        $paidOut = abs((float) $vendor->transactions()
            ->where('type', 'payout')
            ->where('status', 'paid')
            ->sum('amount'));

        return [
            'pending' => $pending,
            'available' => $available,
            'paid' => $paidOut,
            'total_earned' => $pending + $available + $paidCredits,
            'pending_clearance' => $pending,
            'available_balance' => $available,
            'total_paid' => $paidOut,
        ];
    }

    public function createSaleCredit(Vendor $vendor, $orderItem, float $amount): VendorTransaction
    {
        $existingCredit = $vendor->transactions()
            ->where('type', 'sale_credit')
            ->where('order_item_id', $orderItem->id)
            ->first();

        if ($existingCredit) {
            return $existingCredit;
        }

        // 15 working days logic
        $availableAt = now()->addWeekdays(15);

        return $vendor->transactions()->create([
            'order_item_id' => $orderItem->id,
            'type' => 'sale_credit',
            'amount' => $amount,
            'description' => "Sale credit for order item #{$orderItem->id}",
            'status' => 'pending',
            'available_at' => $availableAt,
        ]);
    }

    public function processPayout(Vendor $vendor, float $amount, string $reference): ?VendorTransaction
    {
        $availableBalance = $this->getBalances($vendor)['available'];

        if ($amount > $availableBalance) {
            return null; // Cannot payout more than available
        }

        // Mark oldest available transactions as paid until we cover the amount
        // Simplification for this MVP: We just create a negative payout transaction
        // that reduces the available balance implicitly (wait, if balance is SUM of available,
        // then a negative available transaction reduces it, or we update existing ones to 'paid').

        // Proper way: Find available transactions, mark as paid.
        $availableTxs = $vendor->transactions()->where('status', 'available')->orderBy('available_at')->get();
        $remainingToPay = $amount;

        foreach ($availableTxs as $tx) {
            if ($remainingToPay <= 0) break;

            if ($tx->amount <= $remainingToPay) {
                $tx->update(['status' => 'paid', 'paid_at' => now(), 'reference' => $reference]);
                $remainingToPay -= $tx->amount;
            } else {
                // Split the transaction
                $vendor->transactions()->create([
                    'order_item_id' => $tx->order_item_id,
                    'type' => $tx->type,
                    'amount' => $tx->amount - $remainingToPay,
                    'description' => $tx->description . " (split)",
                    'status' => 'available',
                    'available_at' => $tx->available_at,
                ]);

                $tx->update(['amount' => $remainingToPay, 'status' => 'paid', 'paid_at' => now(), 'reference' => $reference]);
                $remainingToPay = 0;
            }
        }

        return $vendor->transactions()->create([
            'type' => 'payout',
            'amount' => -$amount,
            'description' => "Payout processed",
            'status' => 'paid',
            'paid_at' => now(),
            'reference' => $reference,
        ]);
    }
}
