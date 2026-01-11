<?php

namespace App\Admin\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

/**
 * 学生管理控制器
 * 教师可以管理学生（增删改查）
 * 系统管理员也可以管理所有学生
 */
class StudentController extends AdminController
{
    /**
     * 页面标题
     *
     * @var string
     */
    protected $title = '学生管理';

    /**
     * 构建列表页面
     * 使用预加载避免 N+1 问题
     * 通过权限控制：只有教师角色可以访问（通过路由权限自动检查）
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new Student());

        $grid->model()->with('teacher');

        $grid->column('id', 'ID')->sortable();
        $grid->column('teacher.name', '所属教师')->display(function ($teacherName) {
            return $teacherName ?: '未分配';
        })->filter('like');
        $grid->column('name', '姓名')->sortable()->filter('like');
        $grid->column('email', '邮箱')->sortable()->filter('like');
        $grid->column('phone', '联系电话')->filter('like');
        $grid->column('student_number', '学号')->filter('like');
        $grid->column('created_at', '创建时间')->sortable()->display(function ($created_at) {
            if (!$created_at) {
                return '';
            }
            // 如果是字符串，直接返回；如果是 Carbon 对象，格式化
            return is_string($created_at) ? $created_at : $created_at->format('Y-m-d H:i:s');
        });
        $grid->column('updated_at', '更新时间')->sortable()->display(function ($updated_at) {
            if (!$updated_at) {
                return '';
            }
            // 如果是字符串，直接返回；如果是 Carbon 对象，格式化
            return is_string($updated_at) ? $updated_at : $updated_at->format('Y-m-d H:i:s');
        });

        // 启用过滤器
        $grid->filter(function ($filter) {
            $filter->like('name', '姓名');
            $filter->like('email', '邮箱');
            $filter->like('phone', '联系电话');
            $filter->like('student_number', '学号');
            $filter->equal('teacher_id', '所属教师')->select(Teacher::pluck('name', 'id'));
            $filter->between('created_at', '创建时间')->datetime();
        });

        return $grid;
    }

    /**
     * 构建详情页面
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Student::with('teacher')->findOrFail($id));

        $show->field('id', 'ID');
        $show->field('teacher.name', '所属教师')->as(function ($teacherName) {
            return $teacherName ?: '未分配';
        });
        $show->field('name', '姓名');
        $show->field('email', '邮箱');
        $show->field('phone', '联系电话');
        $show->field('student_number', '学号');
        $show->field('email_verified_at', '邮箱验证时间')->as(function ($value) {
            return $value ? $value->format('Y-m-d H:i:s') : '未验证';
        });
        $show->field('created_at', '创建时间');
        $show->field('updated_at', '更新时间');

        return $show;
    }

    /**
     * 构建表单页面
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Student());

        // 教师选择：有权限访问学生管理的用户可以选择所有教师
        // 权限控制已通过 Laravel Admin 的角色权限系统自动处理
        $form->select('teacher_id', '所属教师')
            ->options(Teacher::pluck('name', 'id'))
            ->required()
            ->rules('required|exists:teachers,id');

        $form->text('name', '姓名')->required()->rules('required|max:255');
        $form->email('email', '邮箱')->required()->rules(function ($form) {
            $rules = 'required|email';
            if (!$form->isCreating()) {
                $rules .= '|unique:students,email,' . $form->model()->id;
            } else {
                $rules .= '|unique:students,email';
            }
            return $rules;
        });
        $form->mobile('phone', '联系电话')->rules('nullable|max:20');
        $form->text('student_number', '学号')->rules(function ($form) {
            $rules = 'nullable|max:50';
            if (!$form->isCreating()) {
                $rules .= '|unique:students,student_number,' . $form->model()->id;
            } else {
                $rules .= '|unique:students,student_number';
            }
            return $rules;
        });

        // 密码字段处理
        $form->password('password', '密码')
            ->help('留空则不修改密码')
            ->rules(function ($form) {
                // 如果是编辑模式且密码为空，则不需要验证
                if (!$form->isCreating() && !$form->password) {
                    return '';
                }
                return 'required|min:6';
            });

        // 保存前的回调，处理密码加密和默认值
        $form->saving(function (Form $form) {
            // 如果密码为空且是编辑模式，则不更新密码
            if (!$form->password && $form->isEditing()) {
                $form->password = $form->model()->password;
            }
        });

        return $form;
    }
}
