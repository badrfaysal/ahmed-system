const fs = require('fs');

let content = fs.readFileSync('app/Http/Controllers/AcController.php', 'utf8');

// 1. Fix product_name in installments insert for sale
const productNamesCode = `
            $productNames = 'مبيعات/تركيب تكييفات';
            if (!empty($inventoryItemsToEncode)) {
                $names = array_column($inventoryItemsToEncode, 'product_name');
                $productNames = implode(' + ', $names);
            }
            // Installments tracking
            if ($request->type == 'sale' || !empty($items)) {
                DB::table('installments')->insert([
                    'sale_type'           => 'inventory',
                    'customer_name'       => $client->name,
                    'product_name'        => $productNames,
`;
content = content.replace(
    /\/\/ Installments tracking\s+if \(\$request->type == 'sale' \|\| !empty\(\$items\)\) \{\s+DB::table\('installments'\)->insert\(\[\s+'sale_type'\s+=> 'inventory',\s+'customer_name'\s+=> \$client->name,\s+'product_name'\s+=> 'مبيعات\/تركيب تكييفات',/g,
    productNamesCode
);

// 2. Fix product_name in installments insert for maintenance
const maintProductNamesCode = `
            } else {
                $maintName = 'صيانة تكييفات';
                if ($request->maintenance_type_name) {
                    $maintName .= ' (' . $request->maintenance_type_name . ')';
                }
                DB::table('installments')->insert([
                    'sale_type'           => 'direct',
                    'customer_name'       => $client->name,
                    'product_name'        => $maintName,
`;
content = content.replace(
    /\} else \{\s+DB::table\('installments'\)->insert\(\[\s+'sale_type'\s+=> 'direct',\s+'customer_name'\s+=> \$client->name,\s+'product_name'\s+=> 'صيانة تكييفات',/g,
    maintProductNamesCode
);

// 3. Fix the JSON response message
content = content.replace(
    /return response\(\)->json\(\['success' => true, 'id' => \$opId\]\);/g,
    "return response()->json(['success' => true, 'id' => \$opId, 'message' => 'تم حفظ العملية بنجاح!']);"
);

fs.writeFileSync('app/Http/Controllers/AcController.php', content, 'utf8');
console.log('Fixed!');
