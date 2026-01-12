<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\Hash;

/**
 * 学生服务类
 * 封装学生相关的业务逻辑
 */
class StudentService
{
    /**
     * 获取学生列表查询构建器（带教师信息）
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getListQuery()
    {
        return Student::with('teacher');
    }

    /**
     * 根据ID获取学生详情（带教师信息）
     *
     * @param int $id
     * @return Student
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getDetailById(int $id): Student
    {
        return Student::with('teacher')->findOrFail($id);
    }

    /**
     * 创建学生
     *
     * @param array $data
     * @return Student
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(array $data): Student
    {
        // 数据清理和验证
        $data = $this->sanitizeData($data);

        // 加密密码
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return Student::create($data);
    }

    /**
     * 更新学生信息
     *
     * @param int $id
     * @param array $data
     * @return Student
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(int $id, array $data): Student
    {
        $student = Student::findOrFail($id);

        // 数据清理和验证
        $data = $this->sanitizeData($data);

        // 如果密码为空，则不更新密码
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        } elseif (isset($data['password'])) {
            // 加密密码
            $data['password'] = Hash::make($data['password']);
        }

        $student->update($data);

        return $student->fresh();
    }

    /**
     * 数据清理
     *
     * @param array $data
     * @return array
     */
    protected function sanitizeData(array $data): array
    {
        // 去除首尾空格
        if (isset($data['name'])) {
            $data['name'] = trim($data['name']);
        }

        if (isset($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
        }

        if (isset($data['phone'])) {
            $data['phone'] = trim($data['phone']);
            // 如果为空，设置为 null
            if (empty($data['phone'])) {
                $data['phone'] = null;
            }
        }

        if (isset($data['student_number'])) {
            $data['student_number'] = trim($data['student_number']);
            // 如果为空，设置为 null
            if (empty($data['student_number'])) {
                $data['student_number'] = null;
            }
        }

        return $data;
    }

    /**
     * 获取邮箱唯一性验证规则
     *
     * @param int|null $excludeId 排除的ID（用于更新时排除自己）
     * @return string
     */
    public function getEmailUniqueRule(?int $excludeId = null): string
    {
        $rule = 'required|email|unique:students,email';
        if ($excludeId) {
            $rule .= ',' . $excludeId;
        }
        return $rule;
    }

    /**
     * 获取学号唯一性验证规则
     *
     * @param int|null $excludeId 排除的ID（用于更新时排除自己）
     * @return string
     */
    public function getStudentNumberUniqueRule(?int $excludeId = null): string
    {
        $rule = 'nullable|max:50|unique:students,student_number';
        if ($excludeId) {
            $rule .= ',' . $excludeId;
        }
        return $rule;
    }

    /**
     * 获取密码验证规则
     *
     * @param bool $isCreating 是否为创建模式
     * @return string
     */
    public function getPasswordRule(bool $isCreating): string
    {
        // 创建时必须提供密码，更新时密码可为空
        return $isCreating ? 'required|min:6' : 'nullable|min:6';
    }
}
