<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     * 为学生表添加 deleted_at 列（如果不存在）
     */
    public function up(): void
    {
        if (!Schema::hasColumn('students', 'deleted_at')) {
            Schema::table('students', function (Blueprint $table) {
                $table->softDeletes()->comment('软删除时间');
            });
            
            // 添加列注释（PostgreSQL）
            DB::statement("COMMENT ON COLUMN students.deleted_at IS '软删除时间'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('students', 'deleted_at')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
