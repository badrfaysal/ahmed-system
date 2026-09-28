<?php
$content = file_get_contents('app/Http/Controllers/OperationsLogController.php');
$target = '// (8)';
$replacement = <<< 'EOT'
            if ($inst->category === 'مبيعات تكييفات' || $inst->product_name === 'صيانة تكييفات') {
                $op = DB::table('ac_operations')->whereBetween('created_at', [$windowStart, $windowEnd])->first();
                if ($op) {
                    DB::table('ac_operation_items')->where('ac_operation_id', $op->id)->delete();
                    DB::table('ac_operations')->where('id', $op->id)->delete();
                }
            }

            // (8)
EOT;
$content = str_replace($target, $replacement, $content);
file_put_contents('app/Http/Controllers/OperationsLogController.php', $content);
