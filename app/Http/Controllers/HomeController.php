<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Support\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $today = Carbon::today();

        $salesToday = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereDate('sales.sold_at', $today)
            ->selectRaw('SUM(sale_items.qty * sale_items.price) as total_sales')
            ->value('total_sales');

        $stats = [
            'spareparts' => Sparepart::count(),
            'low_stock' => Sparepart::whereColumn('stock', '<=', 'min_stock')->count(),
            'sales_today' => (float) ($salesToday ?? 0),
            'purchases_today' => Purchase::whereDate('purchased_at', $today)->sum('total'),
            'users' => User::count(),
        ];

        return view('dashboard', compact('stats'));
    }
}
