<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * 教师模型
 * 
 * @property int $id 教师ID
 * @property string $name 教师姓名
 * @property string $email 教师邮箱
 * @property string|null $phone 联系电话
 * @property \Illuminate\Support\Carbon|null $email_verified_at 邮箱验证时间
 * @property string $password 密码
 * @property string|null $remember_token 记住我令牌
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property \Illuminate\Support\Carbon|null $deleted_at 软删除时间
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Student> $students 关联的学生集合
 */
class Teacher extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * 可批量赋值的属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
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
     * 获取该教师的所有学生
     * 一对多关系：一个教师可以管理多个学生
     *
     * @return HasMany
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * 获取该教师创建的所有课程
     * 一对多关系：一个教师可以创建多个课程
     *
     * @return HasMany
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
