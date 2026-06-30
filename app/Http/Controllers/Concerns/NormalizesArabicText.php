<?php

namespace App\Http\Controllers\Concerns;

trait NormalizesArabicText
{
    // توحيد فروق الحروف العربية الشائعة في الكتابة قبل المقارنة:
    // أ/إ/آ/ٱ = ا ، ى = ي ، ة = ه ، وبالتالي يي = ي = ىى = ى تلقائياً
    protected function normalizeArabicTerm(?string $text): string
    {
        if ($text === null || trim($text) === '') return '';

        $text = strtr($text, [
            'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا',
            'ى' => 'ي',
            'ة' => 'ه',
        ]);

        return preg_replace('/ي{2,}/u', 'ي', $text);
    }

    // نفس التطبيع لكن كـ SQL expression تُطبَّق على عمود في قاعدة البيانات
    protected function normalizeArabicColumn(string $column): string
    {
        $expr = $column;
        foreach (['أ', 'إ', 'آ', 'ٱ'] as $variant) {
            $expr = "REPLACE({$expr}, '{$variant}', 'ا')";
        }
        $expr = "REPLACE({$expr}, 'ى', 'ي')";
        $expr = "REPLACE({$expr}, 'ة', 'ه')";
        // تجميع أي "يي" متكررة في حرف واحد (مرتين تغطي حتى 4 تكرارات متتالية)
        $expr = "REPLACE(REPLACE({$expr}, 'يي', 'ي'), 'يي', 'ي')";

        return $expr;
    }

    // تطبيق بحث بنص حر على أكثر من عمود مع تطبيع الحروف العربية في الطرفين
    protected function applyArabicSearch($query, array $columns, ?string $term): void
    {
        $term = $this->normalizeArabicTerm($term);
        if ($term === '') return;

        $query->where(function ($q) use ($columns, $term) {
            foreach ($columns as $column) {
                $q->orWhereRaw($this->normalizeArabicColumn($column) . ' LIKE ?', ["%{$term}%"]);
            }
        });
    }
}
