<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ManualInvoiceController extends SystemController
{
    /**
     * التأكد من وجود جدول الفواتير اليدوية في قاعدة البيانات تلقائياً
     */
    private function ensureTableExists(): void
    {
        if (!Schema::hasTable('manual_invoices')) {
            Schema::create('manual_invoices', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number', 50)->unique();
                $table->string('invoice_title', 190)->default('فاتورة مبيعات');
                $table->string('client_name', 190);
                $table->string('client_phone', 60)->nullable();
                $table->string('client_address', 255)->nullable();
                $table->date('invoice_date');
                $table->longText('items_json');
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('tax_rate', 5, 2)->default(0);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('grand_total', 15, 2)->default(0);
                $table->decimal('paid_amount', 15, 2)->nullable();
                $table->decimal('remaining_amount', 15, 2)->nullable();
                $table->text('notes')->nullable();
                $table->text('terms')->nullable();
                $table->string('status', 30)->default('active'); // active, edited, deleted
                $table->boolean('is_edited')->default(false);
                $table->unsignedInteger('edit_count')->default(0);
                $table->timestamp('last_edited_at')->nullable();
                $table->string('last_edited_by', 120)->nullable();
                $table->longText('edit_history_json')->nullable();
                $table->boolean('is_deleted')->default(false);
                $table->timestamp('deleted_at')->nullable();
                $table->string('deleted_by', 120)->nullable();
                $table->string('delete_reason', 255)->nullable();
                $table->string('created_by_name', 120)->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * الصفحة الرئيسية للفواتير اليدوية (إنشاء فاتورة + سجل الفواتير)
     */
    public function index(Request $request)
    {
        $this->ensureTableExists();

        $invoices = DB::table('manual_invoices')
            ->orderByDesc('id')
            ->get()
            ->map(function ($inv) {
                $inv->items = json_decode($inv->items_json ?: '[]', true) ?: [];
                $inv->edit_history = json_decode($inv->edit_history_json ?: '[]', true) ?: [];
                $inv->items_summary = collect($inv->items)->pluck('title')->filter()->implode(' ، ');
                return $inv;
            });

        $stats = [
            'total'   => $invoices->count(),
            'active'  => $invoices->where('is_deleted', 0)->count(),
            'edited'  => $invoices->where('is_edited', 1)->where('is_deleted', 0)->count(),
            'deleted' => $invoices->where('is_deleted', 1)->count(),
            'amount'  => $invoices->where('is_deleted', 0)->sum('grand_total'),
        ];

        // اقتراحات العملاء والوحدات لتسهيل الإدخال
        $knownClients = collect();
        if (Schema::hasTable('customers')) {
            $knownClients = $knownClients->merge(
                DB::table('customers')->select('name', 'phone', 'address')->limit(300)->get()
            );
        }
        $prevClients = DB::table('manual_invoices')
            ->select('client_name as name', 'client_phone as phone', 'client_address as address')
            ->whereNotNull('client_name')
            ->orderByDesc('id')
            ->limit(200)
            ->get();
        $clientsList = $knownClients->merge($prevClients)->unique('name')->values();

        $units = ['عدد', 'قطعة', 'متر', 'متر مربع', 'متر طولي', 'مقطوع', 'طن', 'كجم', 'كرتونة', 'شكارة', 'لفة', 'طقم', 'يوم', 'ساعة'];
        if (Schema::hasTable('cont_units')) {
            $dbUnits = DB::table('cont_units')->pluck('name')->toArray();
            $units = array_values(array_unique(array_merge($units, $dbUnits)));
        }

        $settings = Schema::hasTable('system_settings')
            ? DB::table('system_settings')->pluck('value', 'key')->toArray()
            : [];

        return view('manual_invoices', compact('invoices', 'stats', 'clientsList', 'units', 'settings'));
    }

    /**
     * حفظ فاتورة يدوية جديدة
     */
    public function store(Request $request)
    {
        $this->ensureTableExists();

        $request->validate([
            'client_name'  => 'required|string|max:190',
            'invoice_date' => 'required|date',
            'items'        => 'required|array|min:1',
        ]);

        $rawItems = $request->input('items', []);
        $cleanItems = [];
        $subtotal = 0;

        foreach ($rawItems as $idx => $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') continue;

            $qty   = max(0.0001, floatval($item['qty'] ?? 1));
            $unit  = trim((string) ($item['unit'] ?? 'عدد')) ?: 'عدد';
            $price = max(0, floatval($item['price'] ?? 0));
            $total = round($qty * $price, 2);

            $cleanItems[] = [
                'index' => count($cleanItems) + 1,
                'title' => $title,
                'qty'   => $qty,
                'unit'  => $unit,
                'price' => $price,
                'total' => $total,
            ];
            $subtotal += $total;
        }

        if (empty($cleanItems)) {
            return response()->json(['success' => false, 'message' => 'يرجى إدخال صنف أو بند واحد على الأقل.'], 422);
        }

        $discount = max(0, floatval($request->input('discount_amount', 0)));
        $taxRate  = max(0, floatval($request->input('tax_rate', 0)));
        $afterDiscount = max(0, $subtotal - $discount);
        $taxAmount = round($afterDiscount * ($taxRate / 100), 2);
        $grandTotal = round($afterDiscount + $taxAmount, 2);

        $paidAmount = $request->filled('paid_amount') && $request->input('paid_amount') !== ''
            ? max(0, floatval($request->input('paid_amount')))
            : null;
        $remainingAmount = $paidAmount !== null ? max(0, round($grandTotal - $paidAmount, 2)) : null;

        $nextId = (DB::table('manual_invoices')->max('id') ?? 0) + 1;
        $invoiceNumber = 'INV-' . date('Ym', strtotime($request->invoice_date)) . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        while (DB::table('manual_invoices')->where('invoice_number', $invoiceNumber)->exists()) {
            $nextId++;
            $invoiceNumber = 'INV-' . date('Ym', strtotime($request->invoice_date)) . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        }

        $user = session('auth_user');

        $id = DB::table('manual_invoices')->insertGetId([
            'invoice_number'   => $invoiceNumber,
            'invoice_title'    => trim((string) $request->input('invoice_title', 'فاتورة مبيعات')) ?: 'فاتورة مبيعات',
            'client_name'      => trim($request->client_name),
            'client_phone'     => trim((string) $request->input('client_phone', '')),
            'client_address'   => trim((string) $request->input('client_address', '')),
            'invoice_date'     => $request->invoice_date,
            'items_json'       => json_encode($cleanItems, JSON_UNESCAPED_UNICODE),
            'subtotal'         => $subtotal,
            'discount_amount'  => $discount,
            'tax_rate'         => $taxRate,
            'tax_amount'       => $taxAmount,
            'grand_total'      => $grandTotal,
            'paid_amount'      => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'notes'            => trim((string) $request->input('notes', '')),
            'terms'            => trim((string) $request->input('terms', '')),
            'status'           => 'active',
            'is_edited'        => false,
            'edit_count'       => 0,
            'is_deleted'       => false,
            'created_by_name'  => $user?->name ?? 'النظام',
            'created_by'       => $user?->id ?? null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $invoice = $this->getFormattedInvoice($id);

        return response()->json([
            'success' => true,
            'message' => "تم إنشاء الفاتورة رقم ({$invoiceNumber}) بنجاح.",
            'invoice' => $invoice,
        ]);
    }

    /**
     * جلب بيانات فاتورة للعرض أو التعديل
     */
    public function show($id)
    {
        $this->ensureTableExists();
        $invoice = $this->getFormattedInvoice($id);
        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'الفاتورة غير موجودة.'], 404);
        }
        return response()->json(['success' => true, 'invoice' => $invoice]);
    }

    /**
     * تعديل فاتورة يدوية مع الاحتفاظ بعلامة وسجل التعديل
     */
    public function update(Request $request, $id)
    {
        $this->ensureTableExists();

        $old = DB::table('manual_invoices')->where('id', $id)->first();
        if (!$old) {
            return response()->json(['success' => false, 'message' => 'الفاتورة غير موجودة.'], 404);
        }

        $request->validate([
            'client_name'  => 'required|string|max:190',
            'invoice_date' => 'required|date',
            'items'        => 'required|array|min:1',
        ]);

        $rawItems = $request->input('items', []);
        $cleanItems = [];
        $subtotal = 0;

        foreach ($rawItems as $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') continue;

            $qty   = max(0.0001, floatval($item['qty'] ?? 1));
            $unit  = trim((string) ($item['unit'] ?? 'عدد')) ?: 'عدد';
            $price = max(0, floatval($item['price'] ?? 0));
            $total = round($qty * $price, 2);

            $cleanItems[] = [
                'index' => count($cleanItems) + 1,
                'title' => $title,
                'qty'   => $qty,
                'unit'  => $unit,
                'price' => $price,
                'total' => $total,
            ];
            $subtotal += $total;
        }

        if (empty($cleanItems)) {
            return response()->json(['success' => false, 'message' => 'يرجى إدخال صنف أو بند واحد على الأقل.'], 422);
        }

        $discount = max(0, floatval($request->input('discount_amount', 0)));
        $taxRate  = max(0, floatval($request->input('tax_rate', 0)));
        $afterDiscount = max(0, $subtotal - $discount);
        $taxAmount = round($afterDiscount * ($taxRate / 100), 2);
        $grandTotal = round($afterDiscount + $taxAmount, 2);

        $paidAmount = $request->filled('paid_amount') && $request->input('paid_amount') !== ''
            ? max(0, floatval($request->input('paid_amount')))
            : null;
        $remainingAmount = $paidAmount !== null ? max(0, round($grandTotal - $paidAmount, 2)) : null;

        $user = session('auth_user');
        $editorName = $user?->name ?? 'الإدارة';

        // حفظ النسخة السابقة في سجل التعديلات
        $history = json_decode($old->edit_history_json ?: '[]', true) ?: [];
        $history[] = [
            'edited_at'      => now()->format('Y-m-d H:i:s'),
            'edited_by'      => $editorName,
            'prev_client'    => $old->client_name,
            'prev_total'     => floatval($old->grand_total),
            'prev_date'      => $old->invoice_date,
            'prev_items_cnt' => count(json_decode($old->items_json ?: '[]', true) ?: []),
        ];

        $newEditCount = intval($old->edit_count) + 1;

        DB::table('manual_invoices')->where('id', $id)->update([
            'invoice_title'     => trim((string) $request->input('invoice_title', $old->invoice_title)) ?: 'فاتورة مبيعات',
            'client_name'       => trim($request->client_name),
            'client_phone'      => trim((string) $request->input('client_phone', '')),
            'client_address'    => trim((string) $request->input('client_address', '')),
            'invoice_date'      => $request->invoice_date,
            'items_json'        => json_encode($cleanItems, JSON_UNESCAPED_UNICODE),
            'subtotal'          => $subtotal,
            'discount_amount'   => $discount,
            'tax_rate'          => $taxRate,
            'tax_amount'        => $taxAmount,
            'grand_total'       => $grandTotal,
            'paid_amount'       => $paidAmount,
            'remaining_amount'  => $remainingAmount,
            'notes'             => trim((string) $request->input('notes', '')),
            'terms'             => trim((string) $request->input('terms', '')),
            'status'            => $old->is_deleted ? 'deleted' : 'edited',
            'is_edited'         => true,
            'edit_count'        => $newEditCount,
            'last_edited_at'    => now(),
            'last_edited_by'    => $editorName,
            'edit_history_json' => json_encode($history, JSON_UNESCAPED_UNICODE),
            'updated_at'        => now(),
        ]);

        $invoice = $this->getFormattedInvoice($id);

        return response()->json([
            'success' => true,
            'message' => "تم حفظ تعديل الفاتورة ({$old->invoice_number}) بنجاح وتم تعليمها كفاتورة معدلة.",
            'invoice' => $invoice,
        ]);
    }

    /**
     * حذف فاتورة (حذف ناعم بحيث تبقى في السجل ويظهر أنها محذوفة)
     */
    public function destroy(Request $request, $id)
    {
        $this->ensureTableExists();

        $inv = DB::table('manual_invoices')->where('id', $id)->first();
        if (!$inv) {
            return response()->json(['success' => false, 'message' => 'الفاتورة غير موجودة.'], 404);
        }

        $user = session('auth_user');
        $deleterName = $user?->name ?? 'الإدارة';

        DB::table('manual_invoices')->where('id', $id)->update([
            'status'        => 'deleted',
            'is_deleted'    => true,
            'deleted_at'    => now(),
            'deleted_by'    => $deleterName,
            'delete_reason' => trim((string) $request->input('reason', 'تم الحذف من سجل الفواتير اليدوية')),
            'updated_at'    => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "تم تعليم الفاتورة ({$inv->invoice_number}) كفاتورة محذوفة في السجل.",
            'invoice' => $this->getFormattedInvoice($id),
        ]);
    }

    private function getFormattedInvoice($id)
    {
        $inv = DB::table('manual_invoices')->where('id', $id)->first();
        if (!$inv) return null;
        $inv->items = json_decode($inv->items_json ?: '[]', true) ?: [];
        $inv->edit_history = json_decode($inv->edit_history_json ?: '[]', true) ?: [];
        $inv->items_summary = collect($inv->items)->pluck('title')->filter()->implode(' ، ');
        $inv->tafqeet = ContractingController::tafqeet($inv->grand_total);
        return $inv;
    }
}
