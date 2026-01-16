<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 课程模型
 * 
 * @property int $id 课程ID
 * @property string $name 课程名
 * @property string $year_month 年月（格式：202310）
 * @property float $fee 课程费用
 * @property int $teacher_id 创建课程的教师ID（关联 admin_users 表）
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property \Illuminate\Support\Carbon|null $deleted_at 软删除时间
 * @property-read Administrator $teacher 创建课程的教师（admin_users 表中的用户）
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Student> $students 关联的学生集合（多对多）
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Invoice> $invoices 关联的账单集合
 */
class Course extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 可批量赋值的属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'year_month',
        'fee',
        'teacher_id',
    ];

    /**
     * 属性类型转换
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fee' => 'decimal:2',
    ];

    /**
     * 获取创建该课程的教师
     * 多对一关系：多个课程属于一个教师
     * 教师现在存储在 admin_users 表中
     *
     * @return BelongsTo
     */
    public function teacher(): BelongsTo
    {
        $usersTable = config('admin.database.users_table', 'admin_users');
        return $this->belongsTo(Administrator::class, 'teacher_id', 'id');
    }

    /**
     * 获取参加该课程的学生
     * 多对多关系：一个课程可以有多个学生，一个学生可以参加多个课程
     *
     * @return BelongsToMany
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'course_student')
            ->withTimestamps();
    }

    /**
     * 获取该课程的账单
     * 一对多关系：一个课程可以有多个账单
     *
     * @return HasMany
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
