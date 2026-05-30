<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\CashRegister;
use App\Models\Category;
use App\Models\DailyProductionCost;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\MaintenanceRequest;
use App\Models\MenuItem;
use App\Models\Module;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BackofficeDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::where('slug', 'main-branch')->firstOrFail();
        $restaurantModule = Module::where('branch_id', $branch->id)
            ->where('slug', 'restaurant')
            ->firstOrFail();
        $adminUser = User::where('email', 'admin@cazera.test')->firstOrFail();

        $categories = collect([
            ['name' => 'Appetizers', 'slug' => 'appetizers', 'description' => 'Light starters to share.', 'sort_order' => 1],
            ['name' => 'Main Courses', 'slug' => 'main-courses', 'description' => 'Hearty main dishes.', 'sort_order' => 2],
            ['name' => 'Desserts', 'slug' => 'desserts', 'description' => 'Sweet endings.', 'sort_order' => 3],
            ['name' => 'Beverages', 'slug' => 'beverages', 'description' => 'Cold and hot drinks.', 'sort_order' => 4],
            ['name' => 'Specials', 'slug' => 'specials', 'description' => 'Chef specials of the day.', 'sort_order' => 5],
        ])->mapWithKeys(function (array $category) use ($branch, $restaurantModule) {
            $category = Category::firstOrCreate([
                'branch_id' => $branch->id,
                'module_id' => $restaurantModule->id,
                'slug' => $category['slug'],
            ], array_merge($category, [
                'branch_id' => $branch->id,
                'module_id' => $restaurantModule->id,
            ]));

            return [$category->slug => $category];
        });

        $menuItems = collect([
            ['name' => 'Fried Plantain', 'slug' => 'fried-plantain', 'description' => 'Golden fried ripe plantains.', 'category' => 'appetizers', 'quantity' => 24, 'price' => 1200.00, 'cost_price' => 450.00, 'preparation_time' => 8, 'status' => 'available', 'is_trackable' => true],
            ['name' => 'Grilled Chicken', 'slug' => 'grilled-chicken', 'description' => 'Garlic and herb grilled chicken.', 'category' => 'main-courses', 'quantity' => 18, 'price' => 4200.00, 'cost_price' => 1800.00, 'preparation_time' => 20, 'status' => 'available', 'is_trackable' => true],
            ['name' => 'Chocolate Mousse', 'slug' => 'chocolate-mousse', 'description' => 'Rich dark chocolate mousse.', 'category' => 'desserts', 'quantity' => 12, 'price' => 1600.00, 'cost_price' => 650.00, 'preparation_time' => 10, 'status' => 'available', 'is_trackable' => false],
            ['name' => 'Mango Smoothie', 'slug' => 'mango-smoothie', 'description' => 'Fresh mango, yogurt and honey.', 'category' => 'beverages', 'quantity' => 30, 'price' => 950.00, 'cost_price' => 350.00, 'preparation_time' => 6, 'status' => 'available', 'is_trackable' => true],
            ['name' => 'Seafood Platter', 'slug' => 'seafood-platter', 'description' => 'Seasonal seafood with sides.', 'category' => 'specials', 'quantity' => 10, 'price' => 9800.00, 'cost_price' => 4500.00, 'preparation_time' => 25, 'status' => 'available', 'is_trackable' => true],
        ])->mapWithKeys(function (array $item) use ($branch, $restaurantModule, $categories) {
            $menuItem = MenuItem::firstOrCreate([
                'branch_id' => $branch->id,
                'module_id' => $restaurantModule->id,
                'slug' => $item['slug'],
            ], [
                'branch_id' => $branch->id,
                'module_id' => $restaurantModule->id,
                'category_id' => $categories[$item['category']]->id,
                'name' => $item['name'],
                'description' => $item['description'],
                'image_url' => null,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'cost_price' => $item['cost_price'],
                'preparation_time' => $item['preparation_time'],
                'status' => $item['status'],
                'is_trackable' => $item['is_trackable'],
                'sort_order' => 0,
            ]);

            return [$menuItem->slug => $menuItem];
        });

        $suppliers = collect([
            ['name' => 'Fresh Farms', 'slug' => 'fresh-farms', 'code' => 'SUP-001', 'contact_name' => 'Ada Nwachukwu', 'email' => 'ada@freshfarms.test', 'phone' => '08011112222', 'address' => '4 Farm Road, Lagos', 'notes' => 'Local produce supplier.'],
            ['name' => 'Beverage House', 'slug' => 'beverage-house', 'code' => 'SUP-002', 'contact_name' => 'Chike Obi', 'email' => 'chike@bevhouse.test', 'phone' => '08033334444', 'address' => '20 Drink Avenue, Lagos', 'notes' => 'Cold drink supplier.'],
            ['name' => 'Golden Bakery', 'slug' => 'golden-bakery', 'code' => 'SUP-003', 'contact_name' => 'Linda Salami', 'email' => 'linda@goldenbakery.test', 'phone' => '08055556666', 'address' => '10 Bread Street, Lagos', 'notes' => 'Bakery and confectionary supplies.'],
            ['name' => 'Prime Proteins', 'slug' => 'prime-proteins', 'code' => 'SUP-004', 'contact_name' => 'Emeka John', 'email' => 'emeka@primeproteins.test', 'phone' => '08077778888', 'address' => '2 Meat Lane, Lagos', 'notes' => 'Fresh meat and protein supplier.'],
            ['name' => 'Spice Works', 'slug' => 'spice-works', 'code' => 'SUP-005', 'contact_name' => 'Fatima Kareem', 'email' => 'fatima@spiceworks.test', 'phone' => '08099990000', 'address' => '1 Spice Road, Lagos', 'notes' => 'Spices and dry goods provider.'],
        ])->mapWithKeys(function (array $supplier) use ($branch, $restaurantModule) {
            $supplier = Supplier::firstOrCreate([
                'branch_id' => $branch->id,
                'slug' => $supplier['slug'],
            ], array_merge($supplier, [
                'branch_id' => $branch->id,
                'module_id' => $restaurantModule->id,
                'is_active' => true,
                'sort_order' => 0,
                'metadata' => ['region' => 'Lagos'],
            ]));

            return [$supplier->slug => $supplier];
        });

        $inventoryCategories = collect([
            ['name' => 'Produce', 'slug' => 'produce', 'description' => 'Fresh fruits and vegetables.'],
            ['name' => 'Dairy', 'slug' => 'dairy', 'description' => 'Milk, cheese and dairy products.'],
            ['name' => 'Meat', 'slug' => 'meat', 'description' => 'Protein and meat items.'],
            ['name' => 'Beverages', 'slug' => 'beverages', 'description' => 'Ingredients for house drinks.'],
            ['name' => 'Bakery', 'slug' => 'bakery', 'description' => 'Breads, pastries and packing.'],
        ])->mapWithKeys(function (array $category) use ($branch, $restaurantModule) {
            $category = InventoryCategory::firstOrCreate([
                'branch_id' => $branch->id,
                'slug' => $category['slug'],
            ], array_merge($category, [
                'branch_id' => $branch->id,
                'module_id' => $restaurantModule->id,
                'is_active' => true,
                'sort_order' => 0,
            ]));

            return [$category->slug => $category];
        });

        collect([
            ['name' => 'Ripe Plantains', 'slug' => 'ripe-plantains', 'sku' => 'INV-001', 'barcode' => '1234567800010', 'description' => 'Sweet yellow plantains.', 'category' => 'produce', 'supplier' => 'fresh-farms', 'unit_cost' => 150.00, 'unit_price' => 250.00, 'quantity_on_hand' => 120, 'reorder_level' => 20, 'reorder_quantity' => 40],
            ['name' => 'Whole Milk', 'slug' => 'whole-milk', 'sku' => 'INV-002', 'barcode' => '1234567800027', 'description' => 'Fresh whole milk.', 'category' => 'dairy', 'supplier' => 'golden-bakery', 'unit_cost' => 320.00, 'unit_price' => 560.00, 'quantity_on_hand' => 80, 'reorder_level' => 15, 'reorder_quantity' => 30],
            ['name' => 'Boneless Chicken', 'slug' => 'boneless-chicken', 'sku' => 'INV-003', 'barcode' => '1234567800034', 'description' => 'Fresh boneless chicken.', 'category' => 'meat', 'supplier' => 'prime-proteins', 'unit_cost' => 900.00, 'unit_price' => 1200.00, 'quantity_on_hand' => 60, 'reorder_level' => 10, 'reorder_quantity' => 20],
            ['name' => 'Mango Puree', 'slug' => 'mango-puree', 'sku' => 'INV-004', 'barcode' => '1234567800041', 'description' => 'Natural mango puree.', 'category' => 'beverages', 'supplier' => 'beverage-house', 'unit_cost' => 420.00, 'unit_price' => 750.00, 'quantity_on_hand' => 40, 'reorder_level' => 8, 'reorder_quantity' => 20],
            ['name' => 'Baking Flour', 'slug' => 'baking-flour', 'sku' => 'INV-005', 'barcode' => '1234567800058', 'description' => 'All-purpose baking flour.', 'category' => 'bakery', 'supplier' => 'spice-works', 'unit_cost' => 240.00, 'unit_price' => 450.00, 'quantity_on_hand' => 100, 'reorder_level' => 25, 'reorder_quantity' => 50],
        ])->each(function (array $item) use ($branch, $restaurantModule, $inventoryCategories, $suppliers) {
            InventoryItem::firstOrCreate([
                'branch_id' => $branch->id,
                'sku' => $item['sku'],
            ], [
                'branch_id' => $branch->id,
                'module_id' => $restaurantModule->id,
                'supplier_id' => $suppliers[$item['supplier']]->id,
                'category_id' => $inventoryCategories[$item['category']]->id,
                'name' => $item['name'],
                'slug' => $item['slug'],
                'barcode' => $item['barcode'],
                'description' => $item['description'],
                'unit_cost' => $item['unit_cost'],
                'unit_price' => $item['unit_price'],
                'quantity_on_hand' => $item['quantity_on_hand'],
                'reorder_level' => $item['reorder_level'],
                'reorder_quantity' => $item['reorder_quantity'],
                'is_trackable' => true,
                'is_active' => true,
                'settings' => ['storage' => 'cold'],
            ]);
        });

        $customers = collect([
            ['name' => 'John Doe', 'email' => 'john.doe@test.com', 'phone' => '08011223344', 'address' => '12 Market Street, Lagos', 'customer_type' => 'regular', 'loyalty_points' => 120, 'total_orders' => 5, 'total_spent' => 18750.00, 'total_debt' => 0.00, 'status' => 'active', 'last_order_at' => Carbon::now()->subDays(2)],
            ['name' => 'Amaka Ike', 'email' => 'amaka.ike@test.com', 'phone' => '08022334455', 'address' => '55 Island Road, Lagos', 'customer_type' => 'vip', 'loyalty_points' => 320, 'total_orders' => 12, 'total_spent' => 78900.00, 'total_debt' => 0.00, 'status' => 'active', 'last_order_at' => Carbon::now()->subDays(1)],
            ['name' => 'Michael Udo', 'email' => 'michael.udo@test.com', 'phone' => '08033445566', 'address' => '8 Palm Avenue, Lagos', 'customer_type' => 'regular', 'loyalty_points' => 45, 'total_orders' => 3, 'total_spent' => 4620.00, 'total_debt' => 150.00, 'status' => 'active', 'last_order_at' => Carbon::now()->subDays(6)],
            ['name' => 'Faith Chukwu', 'email' => 'faith.chukwu@test.com', 'phone' => '08044556677', 'address' => '33 Bridge Street, Lagos', 'customer_type' => 'regular', 'loyalty_points' => 90, 'total_orders' => 7, 'total_spent' => 24200.00, 'total_debt' => 0.00, 'status' => 'active', 'last_order_at' => Carbon::now()->subDays(3)],
            ['name' => 'Samuel Owusu', 'email' => 'samuel.owusu@test.com', 'phone' => '08055667788', 'address' => '17 Hill Road, Lagos', 'customer_type' => 'corporate', 'loyalty_points' => 215, 'total_orders' => 10, 'total_spent' => 54500.00, 'total_debt' => 0.00, 'status' => 'active', 'last_order_at' => Carbon::now()->subDays(4)],
        ])->mapWithKeys(function (array $customer) use ($branch) {
            $customer = Customer::firstOrCreate([
                'branch_id' => $branch->id,
                'email' => $customer['email'],
            ], array_merge($customer, [
                'branch_id' => $branch->id,
            ]));

            return [$customer->email => $customer];
        });

        $branchSlugs = ['main-branch', 'east-branch', 'west-branch', 'north-branch', 'south-branch'];
        $branches = Branch::whereIn('slug', $branchSlugs)->get()->keyBy('slug');

        $cashRegisterData = [
            ['branch' => 'main-branch', 'name' => 'Main POS Station', 'notes' => 'Seeded main cash register.'],
            ['branch' => 'east-branch', 'name' => 'East POS Station', 'notes' => 'Seeded east branch cash register.'],
            ['branch' => 'west-branch', 'name' => 'West POS Station', 'notes' => 'Seeded west branch cash register.'],
            ['branch' => 'north-branch', 'name' => 'North POS Station', 'notes' => 'Seeded north branch cash register.'],
            ['branch' => 'south-branch', 'name' => 'South POS Station', 'notes' => 'Seeded south branch cash register.'],
        ];

        $cashRegisters = collect($cashRegisterData)->mapWithKeys(function (array $registerData) use ($branches, $restaurantModule, $adminUser) {
            $branch = $branches[$registerData['branch']];

            $cashRegister = CashRegister::firstOrCreate([
                'branch_id' => $branch->id,
                'name' => $registerData['name'],
            ], [
                'module_id' => $restaurantModule->id,
                'opened_by' => $adminUser->id,
                'opening_balance' => 5000.00,
                'expected_balance' => 5000.00,
                'actual_balance' => 5000.00,
                'difference' => 0.00,
                'is_open' => true,
                'opened_at' => Carbon::now()->subDays(7),
                'notes' => $registerData['notes'],
            ]);

            return [$registerData['name'] => $cashRegister];
        });

        $cashRegister = $cashRegisters['Main POS Station'];

        $salesData = [
            [
                'sale_number' => 'ORD-2026-1001',
                'customer_email' => 'john.doe@test.com',
                'type' => 'dine_in',
                'status' => 'completed',
                'items' => [
                    ['menu_item' => 'fried-plantain', 'qty' => 2],
                    ['menu_item' => 'mango-smoothie', 'qty' => 1],
                ],
                'sale_date' => Carbon::now()->subDays(5),
                'notes' => 'Table 4 lunch service.',
            ],
            [
                'sale_number' => 'ORD-2026-1002',
                'customer_email' => 'amaka.ike@test.com',
                'type' => 'takeaway',
                'status' => 'completed',
                'items' => [
                    ['menu_item' => 'grilled-chicken', 'qty' => 1],
                    ['menu_item' => 'chocolate-mousse', 'qty' => 1],
                ],
                'sale_date' => Carbon::now()->subDays(4),
                'notes' => 'Takeaway order for one.',
            ],
            [
                'sale_number' => 'ORD-2026-1003',
                'customer_email' => 'michael.udo@test.com',
                'type' => 'delivery',
                'status' => 'completed',
                'items' => [
                    ['menu_item' => 'seafood-platter', 'qty' => 1],
                ],
                'sale_date' => Carbon::now()->subDays(3),
                'notes' => 'Delivery with special request.',
            ],
            [
                'sale_number' => 'ORD-2026-1004',
                'customer_email' => 'faith.chukwu@test.com',
                'type' => 'online',
                'status' => 'completed',
                'items' => [
                    ['menu_item' => 'mango-smoothie', 'qty' => 2],
                    ['menu_item' => 'fried-plantain', 'qty' => 1],
                ],
                'sale_date' => Carbon::now()->subDays(2),
                'notes' => 'Online pick-up order.',
            ],
            [
                'sale_number' => 'ORD-2026-1005',
                'customer_email' => 'samuel.owusu@test.com',
                'type' => 'dine_in',
                'status' => 'confirmed',
                'items' => [
                    ['menu_item' => 'grilled-chicken', 'qty' => 2],
                ],
                'sale_date' => Carbon::now()->subDay(),
                'notes' => 'Dinner reservation order.',
            ],
        ];

        foreach ($salesData as $saleData) {
            $lineItems = collect($saleData['items'])->map(function (array $item) use ($menuItems, $branch, $restaurantModule) {
                $menuItem = $menuItems[$item['menu_item']];
                $subtotal = round($menuItem->price * $item['qty'], 2);

                return [
                    'menu_item_id' => $menuItem->id,
                    'branch_id' => $branch->id,
                    'module_id' => $restaurantModule->id,
                    'item_name' => $menuItem->name,
                    'sku' => $menuItem->slug,
                    'qty' => $item['qty'],
                    'unit_price' => $menuItem->price,
                    'tax' => 0.00,
                    'discount' => 0.00,
                    'subtotal' => $subtotal,
                    'total' => $subtotal,
                    'status' => 'served',
                    'notes' => null,
                ];
            });

            $subtotal = $lineItems->sum('subtotal');
            $tax = 0.00;
            $discount = 0.00;
            $serviceCharge = 0.00;
            $total = $subtotal + $tax + $serviceCharge - $discount;
            $paidAmount = $total;

            $sale = Sale::updateOrCreate([
                'branch_id' => $branch->id,
                'sale_number' => $saleData['sale_number'],
            ], [
                'module_id' => $restaurantModule->id,
                'customer_id' => $customers[$saleData['customer_email']]->id,
                'created_by' => $adminUser->id,
                'type' => $saleData['type'],
                'status' => $saleData['status'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'service_charge' => $serviceCharge,
                'total' => $total,
                'paid_amount' => $paidAmount,
                'remaining_balance' => 0.00,
                'is_debt' => false,
                'sale_date' => $saleData['sale_date'],
                'served_at' => $saleData['sale_date']->copy()->addMinutes(20),
                'completed_at' => $saleData['sale_date']->copy()->addMinutes(40),
                'notes' => $saleData['notes'],
            ]);

            foreach ($lineItems as $lineItem) {
                SaleItem::updateOrCreate([
                    'sale_id' => $sale->id,
                    'menu_item_id' => $lineItem['menu_item_id'],
                ], array_merge($lineItem, [
                    'sale_id' => $sale->id,
                ]));
            }

            Payment::updateOrCreate([
                'sale_id' => $sale->id,
                'method' => 'cash',
            ], [
                'branch_id' => $branch->id,
                'module_id' => $restaurantModule->id,
                'cash_register_id' => $cashRegister->id,
                'received_by' => $adminUser->id,
                'amount' => $paidAmount,
                'transaction_reference' => 'TXN-' . $sale->sale_number,
                'status' => 'completed',
                'notes' => 'Seeded payment for ' . $sale->sale_number,
                'paid_at' => $saleData['sale_date']->copy()->addMinutes(10),
            ]);
        }

        $expenseCategories = collect([
            ['name' => 'Utilities', 'description' => 'Electricity, water, gas bills.'],
            ['name' => 'Rent', 'description' => 'Branch rental payments.'],
            ['name' => 'Staff Costs', 'description' => 'Salaries and staff benefits.'],
            ['name' => 'Supplies', 'description' => 'Kitchen and cleaning supplies.'],
            ['name' => 'Marketing', 'description' => 'Advertising and promotions.'],
        ])->mapWithKeys(function (array $category) use ($branch, $restaurantModule) {
            $category = ExpenseCategory::firstOrCreate([
                'branch_id' => $branch->id,
                'module_id' => $restaurantModule->id,
                'name' => $category['name'],
            ], [
                'description' => $category['description'],
                'is_active' => true,
            ]);

            return [$category->name => $category];
        });

        collect([
            ['category' => 'Utilities', 'title' => 'Electricity bill', 'amount' => 52000.00, 'expense_date' => Carbon::now()->subDays(6)],
            ['category' => 'Rent', 'title' => 'Shop rent payment', 'amount' => 150000.00, 'expense_date' => Carbon::now()->subDays(15)],
            ['category' => 'Staff Costs', 'title' => 'Weekend pay', 'amount' => 82000.00, 'expense_date' => Carbon::now()->subDays(10)],
            ['category' => 'Supplies', 'title' => 'Kitchen supplies restock', 'amount' => 38000.00, 'expense_date' => Carbon::now()->subDays(3)],
            ['category' => 'Marketing', 'title' => 'Social media campaign', 'amount' => 22000.00, 'expense_date' => Carbon::now()->subDays(8)],
        ])->each(function (array $expense) use ($branch, $restaurantModule, $expenseCategories, $adminUser) {
            Expense::firstOrCreate([
                'branch_id' => $branch->id,
                'expense_category_id' => $expenseCategories[$expense['category']]->id,
                'expense_date' => $expense['expense_date']->toDateString(),
                'title' => $expense['title'],
            ], [
                'module_id' => $restaurantModule->id,
                'recorded_by' => $adminUser->id,
                'amount' => $expense['amount'],
                'notes' => 'Seed data expense for ' . $expense['title'],
                'is_locked' => false,
            ]);
        });

        collect([
            ['title' => 'Vegetable prep', 'amount' => 12000.00, 'production_date' => Carbon::now()->subDays(6)],
            ['title' => 'Morning prep', 'amount' => 9800.00, 'production_date' => Carbon::now()->subDays(5)],
            ['title' => 'Midweek batch', 'amount' => 11200.00, 'production_date' => Carbon::now()->subDays(4)],
            ['title' => 'Weekend menu', 'amount' => 15600.00, 'production_date' => Carbon::now()->subDays(3)],
            ['title' => 'Closing prep', 'amount' => 8300.00, 'production_date' => Carbon::now()->subDays(1)],
        ])->each(function (array $production) use ($branch, $restaurantModule, $adminUser) {
            DailyProductionCost::firstOrCreate([
                'branch_id' => $branch->id,
                'production_date' => $production['production_date']->toDateString(),
                'title' => $production['title'],
            ], [
                'module_id' => $restaurantModule->id,
                'recorded_by' => $adminUser->id,
                'amount' => $production['amount'],
                'notes' => 'Seeded production cost entry.',
                'is_locked' => false,
            ]);
        });

        collect([
            [
                'equipment_name' => 'Refrigerator compressor',
                'description' => 'Replace compressor unit and run diagnostics.',
                'type' => 'repair',
                'priority' => 'high',
                'status' => 'completed',
                'estimated_cost' => 90000.00,
                'actual_cost' => 87500.00,
                'requested_date' => Carbon::now()->subDays(12),
                'approved_date' => Carbon::now()->subDays(11),
                'scheduled_date' => Carbon::now()->subDays(10),
                'completed_date' => Carbon::now()->subDays(9),
                'rejection_reason' => null,
            ],
            [
                'equipment_name' => 'Air conditioner filter',
                'description' => 'Inspect and replace filters.',
                'type' => 'inspection',
                'priority' => 'medium',
                'status' => 'approved',
                'estimated_cost' => 12000.00,
                'actual_cost' => null,
                'requested_date' => Carbon::now()->subDays(8),
                'approved_date' => Carbon::now()->subDays(7),
                'scheduled_date' => Carbon::now()->subDays(6),
                'completed_date' => null,
                'rejection_reason' => null,
            ],
            [
                'equipment_name' => 'Backup generator',
                'description' => 'Preventive maintenance before peak season.',
                'type' => 'preventive',
                'priority' => 'urgent',
                'status' => 'in_progress',
                'estimated_cost' => 45000.00,
                'actual_cost' => null,
                'requested_date' => Carbon::now()->subDays(5),
                'approved_date' => Carbon::now()->subDays(4),
                'scheduled_date' => Carbon::now()->subDays(3),
                'completed_date' => null,
                'rejection_reason' => null,
            ],
            [
                'equipment_name' => 'Kitchen door handle',
                'description' => 'Replace broken handle and secure door.',
                'type' => 'replacement',
                'priority' => 'low',
                'status' => 'requested',
                'estimated_cost' => 5000.00,
                'actual_cost' => null,
                'requested_date' => Carbon::now()->subDays(2),
                'approved_date' => null,
                'scheduled_date' => null,
                'completed_date' => null,
                'rejection_reason' => null,
            ],
            [
                'equipment_name' => 'Delivery vehicle tires',
                'description' => 'Replace worn tires and inspect alignment.',
                'type' => 'repair',
                'priority' => 'high',
                'status' => 'rejected',
                'estimated_cost' => 28000.00,
                'actual_cost' => null,
                'requested_date' => Carbon::now()->subDays(7),
                'approved_date' => null,
                'scheduled_date' => null,
                'completed_date' => null,
                'rejection_reason' => 'Vendor unavailable for emergency number of tires.',
            ],
        ])->each(function (array $request) use ($branch, $restaurantModule, $adminUser) {
            MaintenanceRequest::firstOrCreate([
                'branch_id' => $branch->id,
                'equipment_name' => $request['equipment_name'],
                'requested_date' => $request['requested_date']->toDateTimeString(),
            ], [
                'module_id' => $restaurantModule->id,
                'description' => $request['description'],
                'type' => $request['type'],
                'priority' => $request['priority'],
                'status' => $request['status'],
                'requested_by' => $adminUser->id,
                'approved_by' => $request['status'] !== 'requested' && $request['status'] !== 'rejected' ? $adminUser->id : null,
                'executed_by' => $request['status'] === 'completed' ? $adminUser->id : null,
                'estimated_cost' => $request['estimated_cost'],
                'actual_cost' => $request['actual_cost'],
                'approved_date' => $request['approved_date'],
                'scheduled_date' => $request['scheduled_date'],
                'completed_date' => $request['completed_date'],
                'rejection_reason' => $request['rejection_reason'],
                'notes' => 'Seeded maintenance request.',
            ]);
        });
    }
}
