<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 教师表单验证请求类
 * 验证教师创建和更新的数据合法性
 */
class TeacherRequest extends FormRequest
{
    /**
     * 确定用户是否有权限执行此请求
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 获取验证规则
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $teacherId = $this->route('teacher') ?? $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:1',
                'regex:/^[\x{4e00}-\x{9fa5}a-zA-Z\s]+$/u', // 只允许中文、英文和空格
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                Rule::unique('teachers', 'email')->ignore($teacherId),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^1[3-9]\d{9}$/', // 中国手机号格式验证
            ],
            'password' => [
                $this->isMethod('POST') ? 'required' : 'nullable', // 创建时必填，更新时可选
                'string',
                'min:6',
                'max:255',
            ],
        ];
    }

    /**
     * 获取自定义验证错误消息
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => '姓名不能为空',
            'name.string' => '姓名必须是字符串',
            'name.max' => '姓名不能超过255个字符',
            'name.min' => '姓名至少需要1个字符',
            'name.regex' => '姓名只能包含中文、英文和空格',
            'email.required' => '邮箱不能为空',
            'email.email' => '邮箱格式不正确',
            'email.max' => '邮箱不能超过255个字符',
            'email.unique' => '该邮箱已被使用',
            'phone.nullable' => '联系电话可以为空',
            'phone.string' => '联系电话必须是字符串',
            'phone.max' => '联系电话不能超过20个字符',
            'phone.regex' => '联系电话格式不正确，请输入11位手机号',
            'password.required' => '密码不能为空',
            'password.string' => '密码必须是字符串',
            'password.min' => '密码至少需要6个字符',
            'password.max' => '密码不能超过255个字符',
        ];
    }

    /**
     * 获取自定义属性名称
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => '姓名',
            'email' => '邮箱',
            'phone' => '联系电话',
            'password' => '密码',
        ];
    }

    /**
     * 准备验证数据
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // 去除首尾空格
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->input('name')),
            ]);
        }

        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim($this->input('email'))),
            ]);
        }

        if ($this->has('phone')) {
            $this->merge([
                'phone' => trim($this->input('phone')),
            ]);
        }
    }
}
