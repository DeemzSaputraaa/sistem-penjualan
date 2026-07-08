<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Role;
use App\Models\Sparepart;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use App\Services\SaleService;
use App\Services\StockService;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FullDummyDataSeeder extends Seeder
{
    private const COUNT = 30;

    public function run(): void
    {
        $this->call(RbacSeeder::class);

        $faker = FakerFactory::create('id_ID');
        $batch = now()->format('YmdHis');

        $roles = Role::query()->get()->keyBy('name');
        $purchaseService = app(PurchaseService::class);
        $saleService = app(SaleService::class);
        $stockService = app(StockService::class);

        $users = $this->createUsers($roles->all(), $batch, $faker);
        $categories = $this->createCategories($batch);
        $suppliers = $this->createSuppliers($batch, $faker);
        $customers = $this->createCustomers($batch, $faker);
        $spareparts = $this->createSpareparts($batch, $categories, $faker);

        $purchasingUser = $users['purchasing'];
        $kasirUser = $users['kasir'];
        $gudangUser = $users['gudang'];

        $this->createPurchases($batch, $spareparts, $suppliers, $purchaseService, $purchasingUser);
        $this->createSales($batch, $customers, $saleService, $kasirUser);
        $this->createAdjustments($batch, $stockService, $gudangUser);

        $this->command?->info('Dummy penuh berhasil dibuat: 30 data utama per modul.');
    }

    private function createUsers(array $roles, string $batch, object $faker): array
    {
        $coreUsers = [];
        $roleOrder = ['admin', 'kasir', 'gudang', 'purchasing', 'owner'];

        foreach ($roleOrder as $roleName) {
            /** @var Role|null $role */
            $role = $roles[$roleName] ?? null;

            if (! $role) {
                continue;
            }

            $user = User::create([
                'name' => ucfirst($roleName) . ' Dummy ' . $batch,
                'email' => $roleName . '.' . $batch . '@dummy.test',
                'password' => Hash::make('Password123!'),
            ]);

            $user->roles()->sync([$role->id]);
            $coreUsers[$roleName] = $user;
        }

        for ($i = 1; $i <= self::COUNT; $i++) {
            $roleName = $roleOrder[($i - 1) % count($roleOrder)];
            /** @var Role|null $role */
            $role = $roles[$roleName] ?? null;

            if (! $role) {
                continue;
            }

            $user = User::create([
                'name' => 'Staff ' . ucfirst($roleName) . ' ' . $i,
                'email' => sprintf('staff.%s.%02d@dummy.test', $batch, $i),
                'password' => Hash::make('Password123!'),
            ]);

            $user->roles()->sync([$role->id]);
        }

        return $coreUsers;
    }

    private function createCategories(string $batch): array
    {
        $categories = [];

        for ($i = 1; $i <= self::COUNT; $i++) {
            $categories[] = Category::create([
                'name' => 'Kategori Dummy ' . $i,
                'code' => sprintf('CAT-%s-%02d', $batch, $i),
            ]);
        }

        return $categories;
    }

    private function createSuppliers(string $batch, object $faker): array
    {
        $suppliers = [];

        for ($i = 1; $i <= self::COUNT; $i++) {
            $suppliers[] = Supplier::create([
                'name' => 'Supplier Dummy ' . $i,
                'phone' => '08' . $faker->numerify('##########'),
                'email' => sprintf('supplier.%s.%02d@dummy.test', $batch, $i),
                'address' => $faker->address(),
            ]);
        }

        return $suppliers;
    }

    private function createCustomers(string $batch, object $faker): array
    {
        $customers = [];

        for ($i = 1; $i <= self::COUNT; $i++) {
            $customers[] = Customer::create([
                'name' => 'Customer Dummy ' . $i,
                'phone' => '08' . $faker->numerify('##########'),
                'email' => sprintf('customer.%s.%02d@dummy.test', $batch, $i),
                'address' => $faker->address(),
            ]);
        }

        return $customers;
    }

    private function createSpareparts(string $batch, array $categories, object $faker): array
    {
        $parts = [
            'Kampas Rem',
            'Oli Mesin',
            'Filter Udara',
            'Aki Motor',
            'Busi Iridium',
            'Rantai',
            'Lampu Depan',
            'Kabel Gas',
            'Bearing Roda',
            'Shockbreaker',
        ];

        $spareparts = [];

        for ($i = 1; $i <= self::COUNT; $i++) {
            $priceBuy = $faker->numberBetween(25000, 250000);
            $priceSell = $priceBuy + $faker->numberBetween(10000, 100000);
            $category = $categories[($i - 1) % count($categories)];

            $spareparts[] = Sparepart::create([
                'sku' => sprintf('SKU-%s-%03d', $batch, $i),
                'name' => $parts[($i - 1) % count($parts)] . ' ' . $i,
                'category_id' => $category->id,
                'unit' => 'pcs',
                'price_buy' => $priceBuy,
                'price_sell' => $priceSell,
                'stock' => 0,
                'min_stock' => $faker->numberBetween(3, 10),
                'description' => 'Data dummy sparepart batch ' . $batch,
                'is_active' => true,
            ]);
        }

        return $spareparts;
    }

    private function createPurchases(
        string $batch,
        array $spareparts,
        array $suppliers,
        PurchaseService $purchaseService,
        User $purchasingUser
    ): void {
        for ($i = 1; $i <= self::COUNT; $i++) {
            $selectedSpareparts = collect($spareparts)->shuffle()->take(rand(2, 4))->values();
            $items = $selectedSpareparts->map(function (Sparepart $sparepart) {
                return [
                    'sparepart_id' => $sparepart->id,
                    'qty' => rand(8, 25),
                    'price' => (float) $sparepart->price_buy,
                ];
            })->all();

            $purchase = $purchaseService->create([
                'purchase_no' => sprintf('PO-%s-%03d', $batch, $i),
                'supplier_id' => $suppliers[($i - 1) % count($suppliers)]->id,
                'status' => 'ordered',
                'notes' => 'Dummy pembelian batch ' . $batch,
                'purchased_at' => now()->subDays(self::COUNT - $i),
                'items' => $items,
            ], $purchasingUser);

            $receivePayload = [
                'items' => $purchase->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'received_qty' => $item->qty,
                    ];
                })->all(),
            ];

            $purchaseService->receive($purchase, $receivePayload, $purchasingUser);
        }
    }

    private function createSales(string $batch, array $customers, SaleService $saleService, User $kasirUser): void
    {
        for ($i = 1; $i <= self::COUNT; $i++) {
            $availableSpareparts = Sparepart::query()
                ->where('stock', '>', 0)
                ->inRandomOrder()
                ->limit(rand(1, 3))
                ->get();

            if ($availableSpareparts->isEmpty()) {
                continue;
            }

            $items = $availableSpareparts->map(function (Sparepart $sparepart) {
                $maxQty = max(1, min(4, $sparepart->stock));
                $qty = rand(1, $maxQty);

                return [
                    'sparepart_id' => $sparepart->id,
                    'qty' => $qty,
                    'price' => (float) $sparepart->price_sell,
                ];
            })->all();

            $total = collect($items)->sum(fn ($item) => $item['qty'] * $item['price']);

            $saleService->create([
                'invoice_no' => sprintf('INV-%s-%03d', $batch, $i),
                'customer_id' => $customers[($i - 1) % count($customers)]->id,
                'paid' => $total + rand(0, 50000),
                'sold_at' => now()->subDays(self::COUNT - $i)->addHours(rand(8, 17)),
                'notes' => 'Dummy penjualan batch ' . $batch,
                'items' => $items,
            ], $kasirUser);
        }
    }

    private function createAdjustments(string $batch, StockService $stockService, User $gudangUser): void
    {
        $spareparts = Sparepart::query()->where('is_active', true)->get()->shuffle()->take(self::COUNT)->values();

        foreach ($spareparts as $index => $sparepart) {
            $delta = $index % 2 === 0 ? rand(1, 3) : rand(-3, -1);

            if ($delta < 0 && $sparepart->stock < abs($delta)) {
                $delta = 1;
            }

            $stockService->adjust(
                $sparepart,
                $delta,
                'adjust',
                'adjustment',
                null,
                $gudangUser,
                'Dummy adjustment batch ' . $batch,
                null
            );
        }
    }
}
