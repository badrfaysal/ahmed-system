p<?php
/**
 * سكريبت تحديث أسعار البيع في فاتورة #838 (الحاجه الهام قايتباي)
 * شغّل السكريبت ده على السيرفر: php fix_838.php
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$inst = \Illuminate\Support\Facades\DB::table('installments')->where('id', 838)->first();
if (!$inst) {
    echo "❌ الفاتورة #838 مش موجودة!\n";
    exit(1);
}

echo "✅ الفاتورة #838 - العميل: {$inst->customer_name}\n";
echo "📦 المنتج: {$inst->product_name}\n";
echo "💰 الإجمالي: {$inst->cash_price}\n\n";

$items = json_decode($inst->inventory_items, true);
if (!$items || !is_array($items)) {
    echo "❌ لا يوجد inventory_items!\n";
    exit(1);
}

echo "=== الأصناف قبل التعديل ===\n";
foreach ($items as $it) {
    echo "  - {$it['product_name']} (x{$it['qty']}) | سعر البيع: " . ($it['selling_price'] ?? 'غير محدد') . "\n";
}

// تحديث الأسعار الصحيحة
foreach ($items as &$it) {
    $name = $it['product_name'];
    if (stripos($name, 'شارب') !== false) {
        $it['selling_price'] = 60000;
    } elseif (stripos($name, 'كريبشن') !== false || stripos($name, 'كريبشين') !== false) {
        $it['selling_price'] = 55000;
    } elseif (stripos($name, 'كاربر') !== false || stripos($name, 'كارير') !== false) {
        $it['selling_price'] = 55000;
    }
}
unset($it);

echo "\n=== الأصناف بعد التعديل ===\n";
$total = 0;
foreach ($items as $it) {
    $lineTotal = $it['qty'] * $it['selling_price'];
    $total += $lineTotal;
    echo "  - {$it['product_name']} (x{$it['qty']}) × {$it['selling_price']} = {$lineTotal}\n";
}
echo "\n💰 المجموع: {$total}\n";

// حفظ التعديل
\Illuminate\Support\Facades\DB::table('installments')->where('id', 838)->update([
    'inventory_items' => json_encode($items, JSON_UNESCAPED_UNICODE),
]);

echo "\n✅ تم تحديث الفاتورة بنجاح!\n";
