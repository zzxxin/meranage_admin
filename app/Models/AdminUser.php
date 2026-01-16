<?php

namespace App\Models;

use App\Models\Course;
use App\Models\Student;
use Encore\Admin\Auth\Database\Administrator;

/**
 * 扩展 Administrator 模型
 * 添加与业务模型的关系（students 和 courses）
 */
class AdminUser extends Administrator
{
    /**
     * 可批量赋值的属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'name',
        'avatar',
        'email',
        'phone',
        'user_type',
    ];

    /**
     * 属性类型转换
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];
    /**
     * 获取该教师的所有学生
     * 一对多关系：一个教师可以管理多个学生
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function students(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Student::class, 'teacher_id');
    }

    /**
     * 获取该教师创建的所有课程
     * 一对多关系：一个教师可以创建多个课程
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function courses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }
}
