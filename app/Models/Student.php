<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * 学生模型
 * 
 * @property int $id 学生ID
 * @property int $teacher_id 所属教师ID
 * @property string $name 学生姓名
 * @property string $email 学生邮箱
 * @property string|null $phone 联系电话
 * @property string|null $student_number 学号
 * @property \Illuminate\Support\Carbon|null $email_verified_at 邮箱验证时间
 * @property string $password 密码
 * @property string|null $remember_token 记住我令牌
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property \Illuminate\Support\Carbon|null $deleted_at 软删除时间
 * @property-read Teacher $teacher 所属的教师
 */
class Student extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * 可批量赋值的属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'teacher_id',
        'name',
        'email',
        'phone',
        'student_number',
        'password',
    ];

    /**
     * 序列化时隐藏的属性
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * 属性类型转换
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * 获取该学生所属的教师
     * 多对一关系：多个学生属于一个教师
     *
     * @return BelongsTo
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * 获取该学生参加的课程
     * 多对多关系：一个学生可以参加多个课程，一个课程可以有多个学生
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function courses(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_student')
            ->withTimestamps();
    }

    /**
     * 获取该学生的账单
     * 一对多关系：一个学生可以有多个账单
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * 获取带教师信息的查询构建器
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function queryWithTeacher()
    {
        return static::with('teacher');
    }

    /**
     * 根据ID获取学生详情（带教师信息）
     *
     * @param int $id
     * @return static
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function findWithTeacher(int $id): self
    {
        return static::with('teacher')->findOrFail($id);
    }
}
