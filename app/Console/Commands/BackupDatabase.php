<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature   = 'backup:database {--keep=30 : عدد النسخ المحفوظة}';
    protected $description = 'يأخذ نسخة احتياطية يومية من قاعدة البيانات';

    public function handle(): int
    {
        $this->info('🗄️  بدء عملية النسخ الاحتياطي...');

        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        $backupDir = storage_path('app/backups');
        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename  = "backup_{$dbName}_{$timestamp}.sql";
        $filepath  = $backupDir . DIRECTORY_SEPARATOR . $filename;

        // البحث عن mysqldump (Windows + Linux)
        $mysqldump = $this->findMysqldump();
        if (!$mysqldump) {
            $this->error('❌ mysqldump غير موجود — جرب تحديد المسار في .env عبر MYSQLDUMP_PATH');
            $this->logBackup(false, 'mysqldump not found', 0);
            return self::FAILURE;
        }

        // تنفيذ mysqldump
        $passArg = $dbPass ? '-p' . escapeshellarg($dbPass) : '';
        $cmd = sprintf(
            '%s --host=%s --port=%s --user=%s %s --single-transaction --quick --routines --triggers --default-character-set=utf8mb4 %s > %s 2>&1',
            escapeshellarg($mysqldump),
            escapeshellarg($dbHost),
            escapeshellarg((string) $dbPort),
            escapeshellarg($dbUser),
            $passArg,
            escapeshellarg($dbName),
            escapeshellarg($filepath)
        );

        $output = [];
        $returnCode = 0;
        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0 || !File::exists($filepath) || File::size($filepath) < 100) {
            $err = implode("\n", $output);
            $this->error('❌ فشل النسخ الاحتياطي: ' . $err);
            if (File::exists($filepath)) File::delete($filepath);
            $this->logBackup(false, $err, 0);
            return self::FAILURE;
        }

        $sizeMB = round(File::size($filepath) / 1024 / 1024, 2);
        $this->info("✅ تم بنجاح: {$filename} ({$sizeMB} MB)");

        // تنظيف النسخ القديمة (نخلي آخر --keep بس)
        $this->cleanupOldBackups($backupDir, (int) $this->option('keep'));

        $this->logBackup(true, $filename, $sizeMB);
        return self::SUCCESS;
    }

    private function findMysqldump(): ?string
    {
        // من .env أولاً
        $env = env('MYSQLDUMP_PATH');
        if ($env && File::exists($env)) return $env;

        // مسارات شائعة في Windows (XAMPP / Laragon / WAMP)
        $candidates = [
            'C:\xampp\mysql\bin\mysqldump.exe',
            'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe',
            'C:\wamp64\bin\mysql\mysql8.0.31\bin\mysqldump.exe',
            'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
        ];
        foreach ($candidates as $p) {
            if (File::exists($p)) return $p;
        }

        // محاولة أخيرة: PATH
        $which = PHP_OS_FAMILY === 'Windows' ? 'where mysqldump 2>NUL' : 'which mysqldump 2>/dev/null';
        $result = @shell_exec($which);
        if ($result) {
            $first = trim(explode("\n", trim($result))[0]);
            if ($first && File::exists($first)) return $first;
        }

        return null;
    }

    private function cleanupOldBackups(string $dir, int $keep): void
    {
        $files = collect(File::files($dir))
            ->filter(fn($f) => str_starts_with($f->getFilename(), 'backup_'))
            ->sortByDesc(fn($f) => $f->getMTime())
            ->values();

        $toDelete = $files->slice($keep);
        foreach ($toDelete as $f) {
            File::delete($f->getPathname());
            $this->line("🗑️  حذف نسخة قديمة: {$f->getFilename()}");
        }
    }

    private function logBackup(bool $success, string $note, float $sizeMB): void
    {
        try {
            DB::table('audit_logs')->insert([
                'user_id'    => null,
                'user_name'  => 'النظام (Scheduler)',
                'user_role'  => 'system',
                'action'     => 'backup',
                'module'     => 'system',
                'summary'    => $success
                    ? "نسخة احتياطية ناجحة ({$sizeMB} MB) — {$note}"
                    : "فشل النسخ الاحتياطي: " . mb_substr($note, 0, 300),
                'severity'   => $success ? 'info' : 'critical',
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // تجاهل لو الجدول لسه ما اتعملش
        }
    }
}
