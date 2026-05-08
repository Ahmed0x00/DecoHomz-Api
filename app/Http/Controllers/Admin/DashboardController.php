<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Review;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $today = now()->startOfDay();
        $week = now()->startOfWeek();
        $month = now()->startOfMonth();

        // Orders counts
        $ordersToday = Order::whereDate('created_at', $today)->count();
        $ordersWeek = Order::where('created_at', '>=', $week)->count();
        $ordersMonth = Order::where('created_at', '>=', $month)->count();
        $ordersTotal = Order::count();

        // Revenue (All non-cancelled orders)
        $revenueToday = Order::whereDate('created_at', $today)
            ->where('status', '!=', 'cancelled')
            ->sum('total');
        $revenueWeek = Order::where('created_at', '>=', $week)
            ->where('status', '!=', 'cancelled')
            ->sum('total');
        $revenueMonth = Order::where('created_at', '>=', $month)
            ->where('status', '!=', 'cancelled')
            ->sum('total');
        $revenueTotal = Order::where('status', '!=', 'cancelled')->sum('total');

        // Delivery Fees (All non-cancelled orders)
        $deliveryFeesMonth = Order::where('created_at', '>=', $month)
            ->where('status', '!=', 'cancelled')
            ->sum('delivery_fee');
        $deliveryFeesTotal = Order::where('status', '!=', 'cancelled')->sum('delivery_fee');

        // Users
        $usersTotal = User::count();
        $usersToday = User::whereDate('created_at', $today)->count();

        // Products
        $productsTotal = Product::count();
        $lowStock = Product::where('stock', '<=', 5)->where('stock', '>', 0)->count();
        $outOfStock = Product::where('stock', '<=', 0)->count();

        // Pending reviews
        $pendingReviews = Review::where('is_approved', false)->count();

        // New contacts
        $newContacts = Contact::where('status', 'new')->count();

        // Order status breakdown
        $orderStatus = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Recent orders (last 10)
        $recentOrders = Order::with(['user:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'user' => $order->user,
                    'total' => $order->total,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'created_at' => $order->created_at,
                ];
            });

        // Top selling products (by order_items count)
        $topProducts = DB::table('order_items')
            ->select('product_id', DB::raw('sum(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $product = Product::with('primaryImage')->find($item->product_id);
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->primaryImage?->image,
                    'total_sold' => (int) $item->total_sold,
                ];
            });

        return response()->json([
            'orders' => [
                'today' => $ordersToday,
                'week' => $ordersWeek,
                'month' => $ordersMonth,
                'total' => $ordersTotal,
                'by_status' => $orderStatus,
            ],
            'revenue' => [
                'today' => round($revenueToday, 2),
                'week' => round($revenueWeek, 2),
                'month' => round($revenueMonth, 2),
                'total' => round($revenueTotal, 2),
                'delivery_fees_total' => round($deliveryFeesTotal, 2),
                'delivery_fees_month' => round($deliveryFeesMonth, 2),
            ],
            'users' => [
                'total' => $usersTotal,
                'today' => $usersToday,
            ],
            'products' => [
                'total' => $productsTotal,
                'low_stock' => $lowStock,
                'out_of_stock' => $outOfStock,
            ],
            'pending_reviews' => $pendingReviews,
            'new_contacts' => $newContacts,
            'recent_orders' => $recentOrders,
            'top_products' => $topProducts,
        ]);
    }

    public function chartsOrders(Request $request): JsonResponse
    {
        $days = $request->integer('days', 30);
        $start = now()->subDays($days)->startOfDay();

        $data = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as orders'),
                DB::raw("SUM(CASE WHEN payment_status = 'full_paid' THEN total ELSE 0 END) as revenue")
            )
            ->where('created_at', '>=', $start)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function chartsRevenue(Request $request): JsonResponse
    {
        $months = $request->integer('months', 12);
        $start = now()->subMonths($months)->startOfMonth();

        $data = Order::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw("SUM(CASE WHEN payment_status = 'full_paid' THEN total ELSE 0 END) as revenue"),
                DB::raw('count(*) as orders')
            )
            ->where('created_at', '>=', $start)
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->orderBy('month', 'asc')
            ->get();

        return response()->json(['data' => $data]);
    }
}
