<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_products' => DB::table('products')->where('status', 'active')->count(),
            'total_orders' => DB::table('orders')->count(),
            'total_revenue' => DB::table('orders')->where('status', 'delivered')->sum('total_amount') ?? 0,
            'pending_orders' => DB::table('orders')->where('status', 'pending')->count(),
        ];

        $recent_orders = DB::table('orders')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'recent_orders'));
    }
}


