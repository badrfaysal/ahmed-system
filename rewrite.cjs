const fs = require('fs');

const code = <?php

namespace App\\Http\\Controllers;

use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\DB;

class AcController extends SystemController
{
    public function index()
    {
        \ = DB::table('ac_clients')->get();
        \ = DB::table('ac_floors')->get();
        \ = DB::table('ac_classes')->get();
        \ = DB::table('sales')->where('inventory_status', 'to_inventory')->where('remaining_quantity', '>', 0)->get();
        \ = DB::table('accounts')->get();

        return view('ac', compact('clients', 'floors', 'classes', 'inventoryItems', 'accounts'));
    }

    public function addSetting(Request \)
    {
        \ = 'ac_' . \->type . 's'; // clients, floors, classes
        if (\->type === 'class') \ = 'ac_classes';
        
        DB::table(\)->insert([
            'name' => \->name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return back()->with('success', '?? ????? ??????? ?????.');
    }

    public function deleteSetting(Request \)
    {
        \ = 'ac_' . \->type . 's';
        if (\->type === 'class') \ = 'ac_classes';

        DB::table(\)->where('id', \->id)->delete();
        return back()->with('success', '?? ??? ??????? ?????.');
    }

    public function storeOperation(Request \)
    {
        DB::beginTransaction();
        try {
            \ = DB::table('ac_clients')->where('id', \->ac_client_id)->first();
            if(!\) throw new \\Exception("?????? ??? ?????");

            \ = 0;
            \ = 0;
            \ = 0;
            \ = floatval(\->input('discount_amount', 0));

            \ = DB::table('ac_operations')->insertGetId([
                'ac_client_id' => \->ac_client_id,
                'ac_floor_id' => \->ac_floor_id,
                'ac_class_id' => \->ac_class_id,
                'type' => \->type,
                'maintenance_type_name' => \->maintenance_type_name,
                'total_amount' => 0,
                'cost_amount' => 0,
                'profit_amount' => 0,
                'discount_amount' => \,
                'date' => now()->toDateString(),
                'created_by' => auth()->id() ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \ = json_decode(\->items, true);
            \ = [];

            if (\ && is_array(\) && count(\) > 0) {
                foreach (\ as \) {
                    \ = DB::table('sales')->where('id', \['id'])->lockForUpdate()->first();
                    if (!\) throw new \\Exception("????? ??? ????? ???????");
                    
                    \ = floatval(\['quantity']);
                    if (\->remaining_quantity < \) {
                        throw new \\Exception("?????? ??? ????? ?????: " . \->product_name);
                    }

                    \ = floatval(\['price'] ?? \['selling_price']);
                    \ = floatval(\->purchase_price);
                    
                    \ = \ * \;
                    \ = \ * \;
                    \ = \ - \;

                    \ += \;
                    \ += \;
                    \ += \;

                    DB::table('sales')->where('id', \['id'])->decrement('remaining_quantity', \);

                    DB::table('ac_operation_items')->insert([
                        'ac_operation_id' => \,
                        'item_id' => \['id'],
                        'quantity' => \,
                        'unit_price' => \,
                        'total_price' => \,
                        'cost_price' => \,
                        'profit' => \,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    \[] = [
                        'sale_id' => \['id'],
                        'product_name' => \->product_name,
                        'qty' => \,
                        'purchase_price' => \,
                        'selling_price' => \,
                    ];
                }
            }

            if (\->type == 'maintenance' && empty(\) && floatval(\->manual_sell_price) > 0) {
                \ = floatval(\->manual_sell_price);
                \ = floatval(\->manual_cost_price);
                \ = \ - \;

                \ += \;
                \ += \;
                \ += \;
            }

            \ -= \;
            \ -= \;
            if (\ < 0) \ = 0;

            DB::table('ac_operations')->where('id', \)->update([
                'total_amount' => \,
                'cost_amount' => \,
                'profit_amount' => \,
            ]);

            // Installments tracking
            if (\->type == 'sale' || !empty(\)) {
                DB::table('installments')->insert([
                    'sale_type'           => 'inventory',
                    'customer_name'       => \->name,
                    'product_name'        => '??????/????? ???????',
                    'category'            => '?????? ???????',
                    'cash_price'          => \,
                    'down_payment'        => \,
                    'installment_months'  => 0,
                    'monthly_installment' => 0,
                    'due_day'             => 1,
                    'status'              => 'paid',
                    'profit'              => \,
                    'inventory_items'     => !empty(\) ? json_encode(\, JSON_UNESCAPED_UNICODE) : null,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            } else {
                DB::table('installments')->insert([
                    'sale_type'           => 'direct',
                    'customer_name'       => \->name,
                    'product_name'        => '????? ???????',
                    'category'            => '????',
                    'cash_price'          => \,
                    'down_payment'        => \,
                    'installment_months'  => 0,
                    'monthly_installment' => 0,
                    'due_day'             => 1,
                    'status'              => 'paid',
                    'profit'              => \,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }

            if (\ > 0) {
                DB::table('accounts')->where('id', \->deposit_account_id)->increment('balance', \);
                \ = \->type == 'sale' ? '?????? ?????? ??????? - ' : '????? ??????? - ';
                \ = \ . \->name;
                DB::table('financial_transactions')->insert([
                    'type' => 'income',
                    'amount' => \,
                    'to_account_id' => \->deposit_account_id,
                    'notes' => \,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (\ > 0) {
                DB::table('financial_transactions')->insert([
                    'type' => 'discount',
                    'amount' => \,
                    'notes' => '??? ??????? ??????: ' . \->name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'id' => \]);
        } catch (\\Exception \) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => \->getMessage()], 400);
        }
    }

    public function reportsAjax(Request \)
    {
        \ = \->search;
        \ = \->start_date;
        \ = \->end_date;

        \ = DB::table('ac_operations')
            ->leftJoin('ac_clients', 'ac_operations.ac_client_id', '=', 'ac_clients.id')
            ->leftJoin('ac_floors', 'ac_operations.ac_floor_id', '=', 'ac_floors.id')
            ->leftJoin('ac_classes', 'ac_operations.ac_class_id', '=', 'ac_classes.id')
            ->select('ac_operations.*', 'ac_clients.name as client_name', 'ac_floors.name as floor_name', 'ac_classes.name as class_name');

        if (\) \->whereDate('ac_operations.date', '>=', \);
        if (\) \->whereDate('ac_operations.date', '<=', \);
        if (\) {
            \->where(function(\) use (\) {
                \->where('ac_clients.name', 'like', "%{\}%")
                  ->orWhere('ac_classes.name', 'like', "%{\}%");
            });
        }

        \ = \->orderBy('date', 'desc')->get();
        \ = \->groupBy('client_name');

        \ = [];
        foreach (\ as \ => \) {
            \ = \->where('type', 'sale');
            \ = \->where('type', 'maintenance');

            \[] = [
                'client_name' => \,
                'total_deals' => \->sum('total_amount'),
                'sales_count' => \->count(),
                'maint_count' => \->count(),
                'sales_total' => \->sum('total_amount'),
                'sales_profit' => \->sum('profit_amount'),
                'maint_total' => \->sum('total_amount'),
                'maint_profit' => \->sum('profit_amount'),
                'total_discounts' => \->sum('discount_amount'),
                'operations' => \
            ];
        }

        \ = \->where('type', 'sale')->sum('total_amount');
        \ = \->where('type', 'maintenance')->sum('total_amount');
        \ = \->sum('profit_amount');

        return view('ac_reports_partial', compact('reports', 'global_sales_total', 'global_maint_total', 'global_profit'));
    }

    public function printInvoice(\)
    {
        \ = DB::table('ac_operations')
            ->leftJoin('ac_clients', 'ac_operations.ac_client_id', '=', 'ac_clients.id')
            ->where('ac_operations.id', \)
            ->select('ac_operations.*', 'ac_clients.name as client_name')
            ->first();

        if (!\) return abort(404);

        \ = DB::table('ac_operation_items')
            ->join('sales', 'ac_operation_items.item_id', '=', 'sales.id')
            ->where('ac_operation_id', \)
            ->select('ac_operation_items.*', 'sales.product_name')
            ->get();

        return view('ac_invoice', compact('operation', 'items'));
    }
}
;

fs.writeFileSync('app/Http/Controllers/AcController.php', code, 'utf8');
