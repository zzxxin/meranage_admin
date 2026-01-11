<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 账单模型
 * 
 * @property int $id 账单ID
 * @property int $course_id 关联的课程ID
 * @property int $student_id 关联的学生ID
 * @property string $year_month 年月（格式：202310）
 * @property float $amount 账单金额
 * @property string $status 账单状态（pending待发送、sent已发送、paid已支付、cancelled已取消）
 * @property \Illuminate\Support\Carbon|null $sent_at 发送时间
 * @property \Illuminate\Support\Carbon|null $paid_at 支付时间
 * @property string|null $remark 备注
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property \Illuminate\Support\Carbon|null $deleted_at 软删除时间
 * @property-read Course $course 关联的课程
 * @property-read Student $student 关联的学生
 */
class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 账单状态常量
     */
    public const STATUS_PENDING = 'pending';    // 待发送
    public const STATUS_SENT = 'sent';          // 已发送
    public const STATUS_PAID = 'paid';          // 已支付
    public const STATUS_CANCELLED = 'cancelled'; // 已取消

    /**
     * 可批量赋值的属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'student_id',
        'year_month',
        'amount',
        'status',
        'sent_at',
        'paid_at',
        'remark',
    ];

    /**
     * 属性类型转换
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * 获取关联的课程
     * 多对一关系：多个账单属于一个课程
     *
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * 获取关联的学生
     * 多对一关系：多个账单属于一个学生
     *
     * @return BelongsTo
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
