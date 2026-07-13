<?php

namespace App\Services;

use App\Models\Product;

class VendorProductService
{
    public function submitForReview(Product $product): bool
    {
        if (in_array($product->vendor_status, ['draft', 'rejected', 'changes_requested'])) {
            $product->update(['vendor_status' => 'submitted']);
            return true;
        }

        return false;
    }

    public function handleCriticalEdit(Product $product): void
    {
        if ($product->vendor_status === 'published') {
            $product->update([
                'vendor_status' => 'under_review',
                'is_active' => false
            ]);
        }
    }
}
