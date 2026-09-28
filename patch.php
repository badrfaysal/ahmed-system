<?php
$lines = file('app/Http/Controllers/AcController.php');
$replacement = <<< 'EOT'
                }
            }

            // Handle manual maintenance without items
            if ($request->type == 'maintenance' && empty($items) && $request->manual_sell_price > 0) {
                $manualSell = floatval($request->manual_sell_price);
                $manualCost = floatval($request->manual_cost_price);
                $manualProfit = $manualSell - $manualCost;

                $totalAmount += $manualSell;
                $costAmount += $manualCost;
                $profitAmount += $manualProfit;
            }

            // Apply Discount
            $totalAmount -= $discountAmount;
            $profitAmount -= $discountAmount;
            
            if ($totalAmount < 0) $totalAmount = 0;

            // Insert into installments so it appears in Inventory Sales Log and Operations Log
            if ($request->type == 'sale' || !empty($items)) {
                DB::table('installments')->insert([
                    'sale_type'           => 'inventory',
                    'customer_name'       => $client->name,
                    'product_name'        => '??????/????? ???????',
                    'category'            => '?????? ???????',
                    'cash_price'          => $totalAmount,
                    'down_payment'        => $totalAmount,
                    'installment_months'  => 0,
                    'monthly_installment' => 0,
                    'due_day'             => 1,
                    'status'              => 'paid',
                    'profit'              => $profitAmount,
                    'inventory_items'     => !empty($inventoryItemsToEncode) ? json_encode($inventoryItemsToEncode, JSON_UNESCAPED_UNICODE) : null,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            } else {
                DB::table('installments')->insert([
                    'sale_type'           => 'direct',
                    'customer_name'       => $client->name,
                    'product_name'        => '????? ???????',
                    'category'            => '????',
                    'cash_price'          => $totalAmount,
                    'down_payment'        => $totalAmount,
                    'installment_months'  => 0,
                    'monthly_installment' => 0,
                    'due_day'             => 1,
                    'status'              => 'paid',
                    'profit'              => $profitAmount,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }
EOT;
array_splice($lines, 150, 37, array_map(function($l){return $l."\n";}, explode("\n", $replacement)));
file_put_contents('app/Http/Controllers/AcController.php', implode('', $lines));
