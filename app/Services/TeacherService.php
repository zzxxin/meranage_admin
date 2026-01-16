<?php

namespace App\Services;

use App\Models\AdminUser;
use Encore\Admin\Auth\Database\Role;
use Illuminate\Support\Facades\Hash;

/**
 * 教师服务类
 * 封装教师相关的业务逻辑
 * 教师现在直接使用 admin_users 表，并自动分配教师角色
 */
class TeacherService
{
    /**
     * 获取教师列表查询构建器（带学生数量统计）
     * 只获取 user_type = 'teacher' 的用户
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getListQuery()
    {
        return AdminUser::where('user_type', 'teacher')
            ->withCount('students')
            ->with('roles');
    }

    /**
     * 根据ID获取教师详情（带学生数量统计）
     *
     * @param int $id
     * @return AdminUser
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getDetailById(int $id): AdminUser
    {
        return AdminUser::withCount('students')->with('roles')->findOrFail($id);
    }

    /**
     * 创建教师（admin_users 表中的用户，并自动分配教师角色）
     *
     * @param array $data
     * @return AdminUser
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(array $data): AdminUser
    {
        // 数据清理和验证
        $data = $this->sanitizeData($data);

        // 设置 username（如果没有提供，使用 email）
        if (!isset($data['username']) || empty($data['username'])) {
            $data['username'] = $data['email'] ?? '';
        }

        // 加密密码
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // 设置用户类型为教师
        $data['user_type'] = 'teacher';

        // 创建用户
        $administrator = AdminUser::create($data);

        // 自动分配"教师"角色
        $teacherRole = Role::where('slug', 'teacher')->first();
        if ($teacherRole && !$administrator->roles()->where('slug', 'teacher')->exists()) {
            $administrator->roles()->attach($teacherRole->id);
        }

        return $administrator;
    }

    /**
     * 更新教师信息
     *
     * @param int $id
     * @param array $data
     * @return AdminUser
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(int $id, array $data): AdminUser
    {
        $administrator = AdminUser::findOrFail($id);

        // 数据清理和验证
        $data = $this->sanitizeData($data);

        // 如果密码为空，则不更新密码
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        } elseif (isset($data['password'])) {
            // 加密密码
            $data['password'] = Hash::make($data['password']);
        }

        // 强制设置用户类型为教师（确保更新后仍然是教师）
        $data['user_type'] = 'teacher';

        $administrator->update($data);

        return $administrator->fresh();
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
        $usersTable = config('admin.database.users_table', 'admin_users');
        $rule = 'required|email|unique:' . $usersTable . ',email';
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

    /**
     * 获取所有教师的ID和名称映射（用于下拉选择）
     * 只返回拥有"教师"角色的用户
     *
     * @return \Illuminate\Support\Collection
     */
    public function getOptionsForSelect()
    {
        // 只返回 user_type = 'teacher' 的用户
        return AdminUser::where('user_type', 'teacher')
            ->pluck('name', 'id');
    }
}
