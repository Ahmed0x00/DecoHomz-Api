<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Review;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Affiliate;
use App\Models\Referral;
use App\Models\Cart;
use App\Models\Contact;
use App\Models\PreOrder;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    private function getDates(Request $request): array
    {
        $period = $request->query('period', '30d');
        $now = now();
        $start = match($period) {
            '7d' => now()->subDays(7)->startOfDay(),
            '30d' => now()->subDays(30)->startOfDay(),
            '90d' => now()->subDays(90)->startOfDay(),
            '12m' => now()->subMonths(12)->startOfDay(),
            'all' => now()->subYears(10),
            default => now()->subDays(30)->startOfDay(),
        };

        $diffInDays = $start->diffInDays($now);
        $prevStart = $start->copy()->subDays($diffInDays);
        $prevEnd = $start->copy()->subSecond();

        if ($period === 'all') {
            $prevStart = $start;
            $prevEnd = $start;
        }

        return [$start, $now, $prevStart, $prevEnd];
    }

    private function calculateChangePct($current, $previous): ?float
    {
        if ($previous == 0) return null;
        return round((($current - $previous) / $previous) * 100, 2);
    }

    public function overview(Request $request): JsonResponse
    {
        [$start, $end, $prevStart, $prevEnd] = $this->getDates($request);

        // Current period stats
        $currentOrders = Order::whereBetween('created_at', [$start, $end])->where('status', '!=', 'cancelled')->count();
        $currentGrossRevenue = Order::whereBetween('created_at', [$start, $end])->where('status', '!=', 'cancelled')->sum('subtotal');
        $currentNetRevenue = Order::whereBetween('created_at', [$start, $end])->where('status', '!=', 'cancelled')->sum(DB::raw('total - delivery_fee'));
        $currentVat = Order::whereBetween('created_at', [$start, $end])->where('status', '!=', 'cancelled')->sum('vat_amount');
        $currentDeliveryFees = Order::whereBetween('created_at', [$start, $end])->where('status', '!=', 'cancelled')->sum('delivery_fee');

        $currentCogs = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.status', '!=', 'cancelled')
            ->sum(DB::raw('products.vendor_price * order_items.quantity'));

        $currentProfitMargin = $currentNetRevenue > 0 ? (($currentNetRevenue - $currentCogs) / $currentNetRevenue) * 100 : 0;

        $currentTotalUsers = Order::whereBetween('created_at', [$start, $end])->distinct('user_id')->whereNotNull('user_id')->count();
        $currentRepeatUsers = Order::select('user_id')->whereBetween('created_at', [$start, $end])->whereNotNull('user_id')->groupBy('user_id')->havingRaw('COUNT(*) > 1')->count();
        $currentRepeatRate = $currentTotalUsers > 0 ? ($currentRepeatUsers / $currentTotalUsers) * 100 : 0;

        $currentRefundRate = $currentOrders > 0 ? (Order::whereBetween('created_at', [$start, $end])->where('refund_status', 'approved')->count() / $currentOrders) * 100 : 0;
        
        $currentPreOrders = PreOrder::whereBetween('created_at', [$start, $end])->count();
        $currentActiveCarts = Cart::where('updated_at', '>', now()->subHours(24))->count();
        $currentPendingContacts = Contact::where('status', 'new')->count();

        // Previous period stats
        $prevOrders = Order::whereBetween('created_at', [$prevStart, $prevEnd])->where('status', '!=', 'cancelled')->count();
        $prevGrossRevenue = Order::whereBetween('created_at', [$prevStart, $prevEnd])->where('status', '!=', 'cancelled')->sum('subtotal');
        $prevNetRevenue = Order::whereBetween('created_at', [$prevStart, $prevEnd])->where('status', '!=', 'cancelled')->sum(DB::raw('total - delivery_fee'));
        $prevVat = Order::whereBetween('created_at', [$prevStart, $prevEnd])->where('status', '!=', 'cancelled')->sum('vat_amount');
        $prevDeliveryFees = Order::whereBetween('created_at', [$prevStart, $prevEnd])->where('status', '!=', 'cancelled')->sum('delivery_fee');

        $prevCogs = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$prevStart, $prevEnd])
            ->where('orders.status', '!=', 'cancelled')
            ->sum(DB::raw('products.vendor_price * order_items.quantity'));

        $prevProfitMargin = $prevNetRevenue > 0 ? (($prevNetRevenue - $prevCogs) / $prevNetRevenue) * 100 : 0;

        $prevTotalUsers = Order::whereBetween('created_at', [$prevStart, $prevEnd])->distinct('user_id')->whereNotNull('user_id')->count();
        $prevRepeatUsers = Order::select('user_id')->whereBetween('created_at', [$prevStart, $prevEnd])->whereNotNull('user_id')->groupBy('user_id')->havingRaw('COUNT(*) > 1')->count();
        $prevRepeatRate = $prevTotalUsers > 0 ? ($prevRepeatUsers / $prevTotalUsers) * 100 : 0;

        $prevRefundRate = $prevOrders > 0 ? (Order::whereBetween('created_at', [$prevStart, $prevEnd])->where('refund_status', 'approved')->count() / $prevOrders) * 100 : 0;
        $prevPreOrders = PreOrder::whereBetween('created_at', [$prevStart, $prevEnd])->count();

        return response()->json([
            'period' => $request->query('period', '30d'),
            'kpis' => [
                'gross_revenue' => ['value' => round($currentGrossRevenue, 2), 'previous' => round($prevGrossRevenue, 2), 'change_pct' => $this->calculateChangePct($currentGrossRevenue, $prevGrossRevenue)],
                'net_revenue' => ['value' => round($currentNetRevenue, 2), 'previous' => round($prevNetRevenue, 2), 'change_pct' => $this->calculateChangePct($currentNetRevenue, $prevNetRevenue)],
                'total_vat' => ['value' => round($currentVat, 2), 'previous' => round($prevVat, 2), 'change_pct' => $this->calculateChangePct($currentVat, $prevVat)],
                'total_delivery_fees' => ['value' => round($currentDeliveryFees, 2), 'previous' => round($prevDeliveryFees, 2), 'change_pct' => $this->calculateChangePct($currentDeliveryFees, $prevDeliveryFees)],
                'profit_margin_pct' => ['value' => round($currentProfitMargin, 2), 'previous' => round($prevProfitMargin, 2), 'change_pct' => $this->calculateChangePct($currentProfitMargin, $prevProfitMargin)],
                'repeat_customer_rate' => ['value' => round($currentRepeatRate, 2), 'previous' => round($prevRepeatRate, 2), 'change_pct' => $this->calculateChangePct($currentRepeatRate, $prevRepeatRate)],
                'refund_rate' => ['value' => round($currentRefundRate, 2), 'previous' => round($prevRefundRate, 2), 'change_pct' => $this->calculateChangePct($currentRefundRate, $prevRefundRate)],
                'pre_order_count' => ['value' => $currentPreOrders, 'previous' => $prevPreOrders, 'change_pct' => $this->calculateChangePct($currentPreOrders, $prevPreOrders)],
                'active_carts' => ['value' => $currentActiveCarts],
                'pending_contacts' => ['value' => $currentPendingContacts]
            ]
        ]);
    }

    public function revenue(Request $request): JsonResponse
    {
        [$start, $end] = $this->getDates($request);

        $dateFormat = $request->query('period') === '12m' || $request->query('period') === 'all' ? '%Y-%m' : '%Y-%m-%d';

        $timeSeries = Order::select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as date"),
                DB::raw("SUM(CASE WHEN status != 'cancelled' THEN total ELSE 0 END) as revenue"),
                DB::raw("SUM(CASE WHEN status != 'cancelled' THEN delivery_fee ELSE 0 END) as delivery_fees"),
                DB::raw("SUM(CASE WHEN status != 'cancelled' THEN (discount + affiliate_discount) ELSE 0 END) as discounts"),
                DB::raw("COUNT(*) as orders")
            )
            ->whereBetween('created_at', [$start, $end])
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '{$dateFormat}')"))
            ->orderBy('date', 'asc')
            ->get();

        $byPaymentMethod = Order::select('payment_method as method', DB::raw("SUM(total) as revenue"), DB::raw("COUNT(*) as orders"))
            ->whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('payment_method')
            ->groupBy('payment_method')
            ->get();

        $byPaymentStatus = Order::select('payment_status as status', DB::raw("SUM(total) as total"), DB::raw("COUNT(*) as count"))
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('payment_status')
            ->get();
            
        $waterfall = Order::select(
            DB::raw("SUM(subtotal) as subtotal"),
            DB::raw("SUM(discount) as discount"),
            DB::raw("SUM(affiliate_discount) as affiliate_discount"),
            DB::raw("SUM(delivery_fee) as delivery_fee"),
            DB::raw("SUM(vat_amount) as vat"),
            DB::raw("SUM(total) as net_total")
        )
        ->whereBetween('created_at', [$start, $end])
        ->where('status', '!=', 'cancelled')
        ->first();

        $dayOfWeek = Order::select(DB::raw("DAYOFWEEK(created_at) as day"), DB::raw("SUM(total) as revenue"))
            ->whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->groupBy(DB::raw("DAYOFWEEK(created_at)"))
            ->orderBy('day')
            ->get();

        $heatmapData = Order::select(DB::raw("DAYOFWEEK(created_at) as day"), DB::raw("HOUR(created_at) as hour"), DB::raw("SUM(total) as revenue"))
            ->whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->groupBy(DB::raw("DAYOFWEEK(created_at)"), DB::raw("HOUR(created_at)"))
            ->get();

        return response()->json([
            'time_series' => $timeSeries,
            'by_payment_method' => $byPaymentMethod,
            'by_payment_status' => $byPaymentStatus,
            'waterfall' => $waterfall,
            'day_of_week' => $dayOfWeek,
            'heatmap' => $heatmapData
        ]);
    }

    public function orders(Request $request): JsonResponse
    {
        [$start, $end] = $this->getDates($request);
        $dateFormat = $request->query('period') === '12m' || $request->query('period') === 'all' ? '%Y-%m' : '%Y-%m-%d';

        $totalCarts = Cart::whereBetween('created_at', [$start, $end])->count();
        $cartsWithItems = Cart::has('items')->whereBetween('created_at', [$start, $end])->count();
        $ordersPlaced = Order::whereBetween('created_at', [$start, $end])->count();
        $ordersDelivered = Order::whereBetween('created_at', [$start, $end])->where('status', 'delivered')->count();

        $funnel = [
            'total_carts' => $totalCarts,
            'carts_with_items' => $cartsWithItems,
            'orders_placed' => $ordersPlaced,
            'orders_delivered' => $ordersDelivered
        ];

        $statusBreakdown = Order::select('status', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('status')
            ->pluck('count', 'status');

        $statusTimeSeries = DB::table('orders')
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as date"),
                DB::raw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing"),
                DB::raw("SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped"),
                DB::raw("SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered"),
                DB::raw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled")
            )
            ->whereBetween('created_at', [$start, $end])
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '{$dateFormat}')"))
            ->orderBy('date', 'asc')
            ->get();

        $transitionTimes = DB::select("
            SELECT 
                AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_total_time
            FROM orders 
            WHERE status = 'delivered' AND created_at BETWEEN ? AND ?
        ", [$start, $end]);

        $refundRequests = Order::whereBetween('created_at', [$start, $end])->whereNotNull('refund_status')->get();
        $refundStats = [
            'total_requests' => $refundRequests->count(),
            'approved' => $refundRequests->where('refund_status', 'approved')->count(),
            'rejected' => $refundRequests->where('refund_status', 'rejected')->count(),
            'pending' => $refundRequests->where('refund_status', 'requested')->count(),
            'total_refunded_amount' => $refundRequests->where('refund_status', 'approved')->sum('total'),
        ];

        $peakHours = Order::select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$start, $end])
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour', 'asc')
            ->get();
            
        $dayOfWeek = Order::select(DB::raw("DAYOFWEEK(created_at) as day"), DB::raw("COUNT(*) as count"))
            ->whereBetween('created_at', [$start, $end])
            ->groupBy(DB::raw("DAYOFWEEK(created_at)"))
            ->orderBy('day', 'asc')
            ->get();

        return response()->json([
            'funnel' => $funnel,
            'status_breakdown' => $statusBreakdown,
            'status_time_series' => $statusTimeSeries,
            'transition_times' => $transitionTimes[0]->avg_total_time ?? 0,
            'refund_stats' => $refundStats,
            'peak_hours' => $peakHours,
            'day_of_week' => $dayOfWeek
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        [$start, $end] = $this->getDates($request);
        $limit = $request->query('limit', 10);
        $daysInPeriod = max(1, $start->diffInDays($end));

        // Top Selling
        $topSellingIds = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.status', '!=', 'cancelled')
            ->select('product_id', DB::raw('SUM(quantity) as units_sold'), DB::raw('SUM(order_items.price * quantity) as revenue'))
            ->groupBy('product_id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        $topSelling = [];
        foreach ($topSellingIds as $item) {
            $product = Product::with('primaryImage', 'reviews')->find($item->product_id);
            if ($product) {
                $margin = $product->vendor_price > 0 ? (($product->price - $product->vendor_price) / $product->price) * 100 : 100;
                $topSelling[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->primaryImage?->image,
                    'units_sold' => (int) $item->units_sold,
                    'revenue' => (float) $item->revenue,
                    'avg_rating' => round($product->reviews()->avg('rating') ?? 0, 1),
                    'margin_pct' => round($margin, 2)
                ];
            }
        }

        // Worst Performing (with stock, low revenue)
        $worstPerforming = Product::where('stock', '>', 0)
            ->where('is_active', true)
            ->with('primaryImage')
            ->get()
            ->map(function ($product) use ($start, $end) {
                $revenue = DB::table('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('order_items.product_id', $product->id)
                    ->whereBetween('orders.created_at', [$start, $end])
                    ->where('orders.status', '!=', 'cancelled')
                    ->sum(DB::raw('order_items.price * order_items.quantity'));
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->primaryImage?->image,
                    'stock' => $product->stock,
                    'revenue' => (float) $revenue
                ];
            })
            ->sortBy('revenue')
            ->take($limit)
            ->values();

        // Stock Velocity
        $stockVelocity = [];
        $activeProducts = Product::where('is_active', true)->where('stock', '>', 0)->get();
        foreach ($activeProducts as $product) {
            $unitsSold = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('order_items.product_id', $product->id)
                ->whereBetween('orders.created_at', [$start, $end])
                ->where('orders.status', '!=', 'cancelled')
                ->sum('quantity');
            
            $velocity = $unitsSold / $daysInPeriod;
            $daysOfStock = $velocity > 0 ? $product->stock / $velocity : 999;
            
            if ($velocity > 0) {
                $stockVelocity[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => $product->stock,
                    'velocity' => round($velocity, 2),
                    'days_of_stock' => round($daysOfStock, 1),
                    'urgency' => $daysOfStock < 7 ? 'critical' : ($daysOfStock < 14 ? 'high' : 'normal')
                ];
            }
        }
        usort($stockVelocity, fn($a, $b) => $a['days_of_stock'] <=> $b['days_of_stock']);
        $stockVelocity = array_slice($stockVelocity, 0, $limit);

        // Dead Stock
        $deadStock = [];
        foreach ($activeProducts as $product) {
            $soldInPeriod = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('order_items.product_id', $product->id)
                ->whereBetween('orders.created_at', [$start, $end])
                ->where('orders.status', '!=', 'cancelled')
                ->count();
                
            if ($soldInPeriod == 0) {
                $deadStock[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => $product->stock,
                    'value' => $product->stock * $product->price
                ];
            }
        }
        usort($deadStock, fn($a, $b) => $b['value'] <=> $a['value']);
        $deadStock = array_slice($deadStock, 0, $limit);

        return response()->json([
            'top_selling' => $topSelling,
            'worst_performing' => $worstPerforming,
            'stock_velocity' => $stockVelocity,
            'dead_stock' => $deadStock
        ]);
    }

    public function customers(Request $request): JsonResponse
    {
        [$start, $end] = $this->getDates($request);
        $dateFormat = $request->query('period') === '12m' || $request->query('period') === 'all' ? '%Y-%m' : '%Y-%m-%d';

        $growthTimeSeries = User::select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as date"),
                DB::raw("COUNT(*) as new_users")
            )
            ->whereBetween('created_at', [$start, $end])
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '{$dateFormat}')"))
            ->orderBy('date', 'asc')
            ->get();

        $runningTotal = User::where('created_at', '<', $start)->count();
        foreach ($growthTimeSeries as $point) {
            $runningTotal += $point->new_users;
            $point->total_users = $runningTotal;
        }

        $totalRegistered = User::whereBetween('created_at', [$start, $end])->count();
        
        $usersWithOrders = DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('user_id')
            ->select('user_id', DB::raw('COUNT(*) as order_count'))
            ->groupBy('user_id')
            ->get();

        $withOrders = $usersWithOrders->count();
        $repeatCustomers = $usersWithOrders->where('order_count', '>', 1)->count();
        $oneTimeBuyers = $withOrders - $repeatCustomers;
        $guestOrders = Order::whereBetween('created_at', [$start, $end])->whereNull('user_id')->count();

        $topSpenders = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.status', '!=', 'cancelled')
            ->select('users.id as user_id', 'users.name', 'users.email', DB::raw('SUM(orders.total) as total_spent'), DB::raw('COUNT(orders.id) as order_count'), DB::raw('DATEDIFF(MAX(orders.created_at), MIN(orders.created_at)) as days_active'))
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get();
            
        foreach($topSpenders as $spender) {
            $monthsActive = max(1, $spender->days_active / 30);
            $spender->clv_annualized = ($spender->total_spent / $monthsActive) * 12;
        }

        $distribution = [
            '1_order' => $oneTimeBuyers,
            '2_3_orders' => $usersWithOrders->whereBetween('order_count', [2, 3])->count(),
            '4_5_orders' => $usersWithOrders->whereBetween('order_count', [4, 5])->count(),
            '6_plus_orders' => $usersWithOrders->where('order_count', '>=', 6)->count(),
        ];
        
        $guestRev = Order::whereBetween('created_at', [$start, $end])->whereNull('user_id')->where('status', '!=', 'cancelled')->sum('total');
        $registeredRev = Order::whereBetween('created_at', [$start, $end])->whereNotNull('user_id')->where('status', '!=', 'cancelled')->sum('total');

        return response()->json([
            'growth_time_series' => $growthTimeSeries,
            'segmentation' => [
                'total_registered' => $totalRegistered,
                'with_orders' => $withOrders,
                'repeat_customers' => $repeatCustomers,
                'one_time_buyers' => $oneTimeBuyers,
                'guest_orders' => $guestOrders
            ],
            'top_spenders' => $topSpenders,
            'orders_per_customer_distribution' => $distribution,
            'revenue_split' => [
                'guest' => $guestRev,
                'registered' => $registeredRev
            ]
        ]);
    }
    
    public function geographic(Request $request): JsonResponse
    {
        [$start, $end] = $this->getDates($request);
        
        $byGovernorate = DB::table('shipping_addresses')
            ->join('orders', 'shipping_addresses.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.status', '!=', 'cancelled')
            ->select('shipping_addresses.governorate', DB::raw('COUNT(orders.id) as orders'), DB::raw('SUM(orders.total) as revenue'), DB::raw('SUM(orders.delivery_fee) as delivery_fees_collected'))
            ->groupBy('shipping_addresses.governorate')
            ->orderByDesc('revenue')
            ->get();
            
        $totalOrders = $byGovernorate->sum('orders');
        
        foreach($byGovernorate as $gov) {
            $gov->avg_order_value = $gov->orders > 0 ? $gov->revenue / $gov->orders : 0;
            $gov->pct_of_total_orders = $totalOrders > 0 ? ($gov->orders / $totalOrders) * 100 : 0;
        }
        
        $freeDeliveryOrders = Order::whereBetween('created_at', [$start, $end])->where('status', '!=', 'cancelled')->where('delivery_fee', 0)->count();
        $paidDeliveryOrders = Order::whereBetween('created_at', [$start, $end])->where('status', '!=', 'cancelled')->where('delivery_fee', '>', 0)->count();
        $avgFee = Order::whereBetween('created_at', [$start, $end])->where('status', '!=', 'cancelled')->where('delivery_fee', '>', 0)->avg('delivery_fee');
        $totalFees = Order::whereBetween('created_at', [$start, $end])->where('status', '!=', 'cancelled')->sum('delivery_fee');

        return response()->json([
            'by_governorate' => $byGovernorate,
            'delivery_fee_impact' => [
                'orders_with_free_delivery' => $freeDeliveryOrders,
                'orders_with_paid_delivery' => $paidDeliveryOrders,
                'avg_fee' => round((float)$avgFee, 2),
                'total_fees_collected' => round((float)$totalFees, 2)
            ]
        ]);
    }
    
    public function vendors(Request $request): JsonResponse
    {
        [$start, $end] = $this->getDates($request);
        
        $totalVendors = Vendor::count();
        $activeVendors = Vendor::where('status', 'active')->count();
        
        $vendorData = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('vendors', 'products.vendor_id', '=', 'vendors.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.status', '!=', 'cancelled')
            ->select(
                'vendors.id',
                'vendors.company_name',
                'vendors.status',
                DB::raw('SUM(order_items.quantity) as total_units_sold'),
                DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue'),
                DB::raw('SUM(products.vendor_price * order_items.quantity) as vendor_earnings')
            )
            ->groupBy('vendors.id', 'vendors.company_name', 'vendors.status')
            ->get();
            
        $totalVendorRevenue = $vendorData->sum('total_revenue');
        $totalPayouts = $vendorData->sum('vendor_earnings');
        $platformCommission = $totalVendorRevenue - $totalPayouts;
        
        foreach($vendorData as $v) {
            $v->platform_cut = $v->total_revenue - $v->vendor_earnings;
            $v->products_listed = Product::where('vendor_id', $v->id)->count();
            $v->products_published = Product::where('vendor_id', $v->id)->where('vendor_status', 'published')->count();
            $v->avg_product_rating = Review::join('products', 'reviews.product_id', '=', 'products.id')->where('products.vendor_id', $v->id)->where('reviews.is_approved', true)->avg('rating');
            $v->violation_count = DB::table('vendor_violations')->where('vendor_id', $v->id)->count();
            $v->severity_points = DB::table('vendor_violations')->where('vendor_id', $v->id)->sum('severity_points');
        }

        return response()->json([
            'summary' => [
                'total_vendors' => $totalVendors,
                'active_vendors' => $activeVendors,
                'total_vendor_revenue' => round($totalVendorRevenue, 2),
                'total_payouts' => round($totalPayouts, 2),
                'platform_commission' => round($platformCommission, 2)
            ],
            'vendors' => $vendorData
        ]);
    }
    
    public function inventory(Request $request): JsonResponse
    {
        // Re-using data from products but providing a dedicated summary
        $totalSkus = Product::count();
        $totalStockValue = DB::table('products')->where('is_active', true)->sum(DB::raw('stock * price'));
        $avgStock = DB::table('products')->where('is_active', true)->avg('stock');
        
        $healthy = Product::where('stock', '>', 10)->where('is_active', true)->count();
        $low = Product::where('stock', '>', 0)->where('stock', '<=', 10)->where('is_active', true)->count();
        $out = Product::where('stock', '<=', 0)->where('is_active', true)->count();

        return response()->json([
            'summary' => [
                'total_sku_count' => $totalSkus,
                'total_stock_value' => round((float)$totalStockValue, 2),
                'avg_stock_per_product' => round((float)$avgStock, 2),
                'products_needing_restock' => $low + $out
            ],
            'stock_health' => [
                'healthy' => $healthy,
                'low' => $low,
                'out' => $out
            ]
        ]);
    }

    public function categories(Request $request): JsonResponse
    {
        [$start, $end, $prevStart, $prevEnd] = $this->getDates($request);

        $categories = Category::withCount('products')->get();
        
        $revenueData = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.status', '!=', 'cancelled')
            ->select(
                'products.category_id', 
                DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue'),
                DB::raw('SUM(order_items.quantity) as total_units_sold')
            )
            ->groupBy('products.category_id')
            ->get()
            ->keyBy('category_id');
            
        $prevRevenueData = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$prevStart, $prevEnd])
            ->where('orders.status', '!=', 'cancelled')
            ->select(
                'products.category_id', 
                DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue')
            )
            ->groupBy('products.category_id')
            ->get()
            ->keyBy('category_id');

        $result = $categories->map(function ($category) use ($revenueData, $prevRevenueData) {
            $rev = $revenueData->get($category->id);
            $prevRev = $prevRevenueData->get($category->id);
            
            $units = $rev ? $rev->total_units_sold : 0;
            $revenue = $rev ? $rev->total_revenue : 0;
            $prevRevenue = $prevRev ? $prevRev->total_revenue : 0;
            $avgPrice = $units > 0 ? $revenue / $units : 0;
            
            $growth = $prevRevenue > 0 ? (($revenue - $prevRevenue) / $prevRevenue) * 100 : ($revenue > 0 ? 100 : 0);

            return [
                'id' => $category->id,
                'name' => $category->name,
                'product_count' => $category->products_count,
                'total_revenue' => round((float) $revenue, 2),
                'total_units_sold' => (int) $units,
                'avg_product_price' => round($avgPrice, 2),
                'growth_pct' => round($growth, 2)
            ];
        });

        return response()->json([
            'categories' => $result
        ]);
    }

    public function marketing(Request $request): JsonResponse
    {
        [$start, $end] = $this->getDates($request);

        // Coupons
        $totalCoupons = Coupon::count();
        $activeCoupons = Coupon::where('is_active', true)->where(function ($q) { $q->whereNull('expires_at')->orWhere('expires_at', '>', now()); })->count();

        $couponUsage = DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('coupon_id')
            ->where('status', '!=', 'cancelled')
            ->select(DB::raw('COUNT(*) as uses'), DB::raw('SUM(discount) as total_discount'), DB::raw('SUM(total) as revenue_with_coupon'))
            ->first();

        $topCoupons = DB::table('orders')
            ->join('coupons', 'orders.coupon_id', '=', 'coupons.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.status', '!=', 'cancelled')
            ->select('coupons.code', DB::raw('COUNT(orders.id) as uses'), DB::raw('SUM(orders.discount) as discount_given'), DB::raw('SUM(orders.total) as revenue_generated'))
            ->groupBy('coupons.id', 'coupons.code')
            ->orderByDesc('uses')
            ->limit(5)
            ->get();
            
        foreach($topCoupons as $tc) {
            $tc->roi = $tc->discount_given > 0 ? $tc->revenue_generated / $tc->discount_given : 0;
        }

        // Affiliates
        $totalAffiliates = Affiliate::count();
        $activeAffiliates = Affiliate::where('is_active', true)->count();

        $affiliateOrders = Order::whereBetween('created_at', [$start, $end])
            ->whereNotNull('referral_id')
            ->where('status', '!=', 'cancelled')
            ->get();
            
        $totalReferralOrders = $affiliateOrders->count();
        $totalReferralRevenue = $affiliateOrders->sum('total');
        
        $totalCommissions = DB::table('referrals')
            ->whereBetween('created_at', [$start, $end])
            ->sum('commission_amount');

        $topAffiliatesIds = DB::table('orders')
            ->join('referrals', 'orders.referral_id', '=', 'referrals.id')
            ->join('affiliates', 'referrals.affiliate_id', '=', 'affiliates.id')
            ->join('users', 'affiliates.user_id', '=', 'users.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.status', '!=', 'cancelled')
            ->select(
                'affiliates.id as affiliate_id',
                'users.name as user_name',
                DB::raw('COUNT(orders.id) as referral_count'),
                DB::raw('SUM(orders.total) as revenue_generated'),
                DB::raw('SUM(referrals.commission_amount) as commission')
            )
            ->groupBy('affiliates.id', 'users.name')
            ->orderByDesc('revenue_generated')
            ->limit(5)
            ->get();
            
        foreach($topAffiliatesIds as $ta) {
            $ta->roi = $ta->commission > 0 ? $ta->revenue_generated / $ta->commission : 0;
        }

        return response()->json([
            'coupons' => [
                'total_coupons' => $totalCoupons,
                'active_coupons' => $activeCoupons,
                'total_usage' => $couponUsage->uses ?? 0,
                'total_discount_given' => round((float) ($couponUsage->total_discount ?? 0), 2),
                'revenue_with_coupon' => round((float) ($couponUsage->revenue_with_coupon ?? 0), 2),
                'top_coupons' => $topCoupons
            ],
            'affiliates' => [
                'total_affiliates' => $totalAffiliates,
                'active_affiliates' => $activeAffiliates,
                'total_referral_orders' => $totalReferralOrders,
                'total_referral_revenue' => round((float) $totalReferralRevenue, 2),
                'total_commissions' => round((float) $totalCommissions, 2),
                'top_affiliates' => $topAffiliatesIds
            ]
        ]);
    }
    
    public function activity(Request $request): JsonResponse
    {
        [$start, $end] = $this->getDates($request);
        
        $actionsToday = DB::table('activity_log')->whereDate('created_at', now()->toDateString())->count();
        
        $actionsByType = DB::table('activity_log')
            ->whereBetween('created_at', [$start, $end])
            ->select('log_name', DB::raw('COUNT(*) as count'))
            ->groupBy('log_name')
            ->pluck('count', 'log_name');
            
        $recentActions = DB::table('activity_log')
            ->leftJoin('users', 'activity_log.causer_id', '=', 'users.id')
            ->whereBetween('activity_log.created_at', [$start, $end])
            ->select('users.name as admin', 'activity_log.description as action', 'activity_log.log_name as subject', 'activity_log.created_at')
            ->orderByDesc('activity_log.created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'admin_actions_today' => $actionsToday,
            'actions_by_type' => $actionsByType,
            'recent_critical_actions' => $recentActions
        ]);
    }

    public function live(Request $request): JsonResponse
    {
        $today = now()->startOfDay();
        
        $ordersToday = Order::where('created_at', '>=', $today)->where('status', '!=', 'cancelled')->count();
        $revenueToday = Order::where('created_at', '>=', $today)->where('status', '!=', 'cancelled')->sum('total');
        $activeCarts = Cart::where('updated_at', '>=', now()->subMinutes(30))->count();
        
        return response()->json([
            'orders_today' => $ordersToday,
            'revenue_today' => round($revenueToday, 2),
            'active_carts' => $activeCarts,
            'online_timestamp' => now()->toIso8601String()
        ]);
    }

    public function export(Request $request)
    {
        $section = $request->query('section', 'overview');
        
        // Very basic mock export functionality for the plan
        // This handles a generic array transformation to CSV
        
        $response = $this->{$section}($request);
        $data = $response->getData(true);
        
        $flatData = [];
        if ($section === 'products' && isset($data['top_selling'])) {
            $flatData = $data['top_selling'];
        } else if ($section === 'customers' && isset($data['top_spenders'])) {
            $flatData = $data['top_spenders'];
        } else if ($section === 'vendors' && isset($data['vendors'])) {
            $flatData = $data['vendors'];
        } else {
            // Default flat structure
            foreach($data as $k => $v) {
                if(is_array($v) && isset($v[0]) && is_array($v[0])) {
                    $flatData = $v;
                    break;
                }
            }
        }
        
        if (empty($flatData)) {
            $flatData = [['Message' => 'No tabular data available for export in this section']];
        }

        $period = $request->query('period', '30d');
        
        return response()->streamDownload(function () use ($flatData) {
            $out = fopen('php://output', 'w');
            
            // Header
            if (!empty($flatData)) {
                fputcsv($out, array_keys($flatData[0]));
            }
            
            // Rows
            foreach ($flatData as $row) {
                fputcsv($out, (array)$row);
            }
            
            fclose($out);
        }, "analytics_{$section}_{$period}.csv", [
            'Content-Type' => 'text/csv',
        ]);
    }
}
