<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 创建账单表
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id()->comment('账单ID');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade')->comment('关联的课程ID，外键关联courses表');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade')->comment('关联的学生ID，外键关联students表');
            $table->string('year_month', 6)->comment('年月（格式：202310）');
            $table->decimal('amount', 10, 2)->comment('账单金额');
            $table->string('status', 20)->default('pending')->comment('账单状态：pending待发送、sent已发送、paid已支付、cancelled已取消');
            $table->timestamp('sent_at')->nullable()->comment('发送时间');
            $table->timestamp('paid_at')->nullable()->comment('支付时间');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();
            $table->softDeletes()->comment('软删除时间');
            
            // 添加索引
            $table->index('course_id');
            $table->index('student_id');
            $table->index('year_month');
            $table->index('status');
            $table->index(['student_id', 'status']);
        });

        // 添加表注释（PostgreSQL）
        DB::statement("COMMENT ON TABLE invoices IS '账单表：针对指定年月的课程费用发起请款，由学生支付课程费用，账单可设定各种状态，一个账单仅对应于一个课程的费用'");
        DB::statement("COMMENT ON COLUMN invoices.id IS '账单ID，主键'");
        DB::statement("COMMENT ON COLUMN invoices.course_id IS '关联的课程ID，外键关联courses表，级联删除'");
        DB::statement("COMMENT ON COLUMN invoices.student_id IS '关联的学生ID，外键关联students表，级联删除'");
        DB::statement("COMMENT ON COLUMN invoices.year_month IS '年月，格式为YYYYMM，例如：202310'");
        DB::statement("COMMENT ON COLUMN invoices.amount IS '账单金额，单位：元'");
        DB::statement("COMMENT ON COLUMN invoices.status IS '账单状态：pending待发送、sent已发送、paid已支付、cancelled已取消'");
        DB::statement("COMMENT ON COLUMN invoices.sent_at IS '发送时间'");
        DB::statement("COMMENT ON COLUMN invoices.paid_at IS '支付时间'");
        DB::statement("COMMENT ON COLUMN invoices.remark IS '备注信息'");
        DB::statement("COMMENT ON COLUMN invoices.created_at IS '创建时间'");
        DB::statement("COMMENT ON COLUMN invoices.updated_at IS '更新时间'");
        DB::statement("COMMENT ON COLUMN invoices.deleted_at IS '软删除时间'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
