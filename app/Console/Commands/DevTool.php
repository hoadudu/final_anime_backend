<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DevTool extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:tool';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dev helper tool';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Chọn công việc cần làm:");
        $this->info("1. Xử lý các bảng/migration liên quan đến anime");
        $this->info("2. Fresh migrate theo file migration được chọn (chỉ 1 file)");
        $this->info("3. Fresh toàn bộ database (XÓA HẾT TẤT CẢ)");
        $this->info("0. Thoát");
        $choice = $this->ask("Nhập số công việc (vd: 1)");

        if ($choice == 1) {
            $this->taskAnime();
        } elseif ($choice == 2) {
            $this->taskFreshByMigrationFile();
        } elseif ($choice == 3) {
            $this->taskFreshAllDatabase();
        } else {
            $this->error("Không có lựa chọn hợp lệ!");
        }
    }

    protected function taskAnime()
    {
        $this->info("👉 Bước 1: Xóa migration có chứa từ 'anime'...");

        $migrationPath = database_path('migrations');
        $files = File::files($migrationPath);
        $deleted = 0;

        foreach ($files as $file) {
            if (str_contains($file->getFilename(), 'anime')) {
                File::delete($file->getPathname());
                $this->line("Đã xóa: " . $file->getFilename());
                $deleted++;
            }
        }

        $this->info("Đã xóa {$deleted} file migration chứa 'anime'.");

        $this->info("👉 Bước 2: Đếm số table trong database có chứa từ 'anime'...");

        $tables = DB::select("SHOW TABLES");
        $dbName = DB::getDatabaseName();
        $keyName = "Tables_in_{$dbName}";

        $animeTables = [];
        foreach ($tables as $table) {
            $tableName = $table->$keyName;
            if (str_contains($tableName, 'anime')) {
                $animeTables[] = $tableName;
            }
        }

        $this->info("Có " . count($animeTables) . " table chứa 'anime'.");
        if (!empty($animeTables)) {
            $this->line("Danh sách: " . implode(', ', $animeTables));
        }

        $this->info("👉 Bước 3: Gọi lệnh migrate:generate...");

        if (!empty($animeTables)) {
            $tablesString = implode(",", $animeTables);
            $this->call('migrate:generate', [
                '--tables' => $tablesString
            ]);
        } else {
            $this->warn("Không có table nào chứa 'anime', bỏ qua migrate:generate.");
        }
    }

    /**
     *  Cong viec so 2: Fresh migrate theo file migration được chọn
     */

    protected function taskFreshByMigrationFile()
    {
        $this->info("👉 Liệt kê danh sách file migration trong database/migrations:");

        $migrationPath = database_path('migrations');
        $files = File::files($migrationPath);

        if (empty($files)) {
            $this->error("Không có file migration nào.");
            return;
        }

        $list = [];
        foreach ($files as $i => $file) {
            $list[$i + 1] = $file->getFilename();
            $this->line(($i + 1) . ". " . $file->getFilename());
        }

        $choice = $this->ask("Nhập số file migration cần fresh migrate:");

        if (!isset($list[$choice])) {
            $this->error("Lựa chọn không hợp lệ!");
            return;
        }

        $selectedFile = $list[$choice];
        $this->info("👉 Bạn đã chọn file: {$selectedFile}");

        // Laravel yêu cầu path tính từ gốc project (không phải absolute path OS)
        $relativePath = "database/migrations/" . $selectedFile;

        $this->warn("⚠️  Lưu ý: Lệnh này sẽ ROLLBACK migration này và chạy lại!");
        $confirm = $this->confirm("Bạn có chắc chắn muốn tiếp tục?");
        
        if (!$confirm) {
            $this->info("Đã hủy thao tác.");
            return;
        }

        $this->info("👉 Bước 1: Rollback migration: {$selectedFile}");
        $this->call('migrate:rollback', [
            '--path' => $relativePath
        ]);

        $this->info("👉 Bước 2: Chạy lại migration: {$selectedFile}");
        $this->call('migrate', [
            '--path' => $relativePath
        ]);

        $this->info("✅ Hoàn thành fresh migration cho file: {$selectedFile}");
    }

    /**
     * Fresh toàn bộ database - XÓA HẾT TẤT CẢ
     */
    protected function taskFreshAllDatabase()
    {
        $this->error("⚠️  CẢNH BÁO: Thao tác này sẽ XÓA TOÀN BỘ DATABASE!");
        $this->error("⚠️  Tất cả dữ liệu sẽ bị mất!");
        
        $confirm1 = $this->confirm("Bạn có CHẮC CHẮN muốn xóa toàn bộ database?");
        if (!$confirm1) {
            $this->info("Đã hủy thao tác.");
            return;
        }

        $confirm2 = $this->confirm("Lần cuối: BẠN CÓ THỰC SỰ MUỐN XÓA TẤT CẢ?");
        if (!$confirm2) {
            $this->info("Đã hủy thao tác.");
            return;
        }

        $this->info("👉 Chạy migrate:fresh (xóa toàn bộ database và chạy lại tất cả migrations)");
        $this->call('migrate:fresh');
        
        $this->info("✅ Đã fresh toàn bộ database!");
    }
}
