<?php

namespace App\Services;

use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;

/**
 * 教师服务类
 * 封装教师相关的业务逻辑
 */
class TeacherService
{
    /**
     * 获取教师列表查询构建器（带学生数量统计）
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getListQuery()
    {
        return Teacher::withCount('students');
    }

    /**
     * 根据ID获取教师详情（带学生数量统计）
     *
     * @param int $id
     * @return Teacher
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getDetailById(int $id): Teacher
    {
        return Teacher::withCount('students')->findOrFail($id);
    }

    /**
     * 创建教师
     *
     * @param array $data
     * @return Teacher
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(array $data): Teacher
    {
        // 数据清理和验证
        $data = $this->sanitizeData($data);

        // 加密密码
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return Teacher::create($data);
    }

    /**
     * 更新教师信息
     *
     * @param int $id
     * @param array $data
     * @return Teacher
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(int $id, array $data): Teacher
    {
        $teacher = Teacher::findOrFail($id);

        // 数据清理和验证
        $data = $this->sanitizeData($data);

        // 如果密码为空，则不更新密码
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        } elseif (isset($data['password'])) {
            // 加密密码
            $data['password'] = Hash::make($data['password']);
        }

        $teacher->update($data);

        return $teacher->fresh();
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
        $rule = 'required|email|unique:teachers,email';
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
