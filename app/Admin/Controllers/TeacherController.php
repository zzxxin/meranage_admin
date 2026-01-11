<?php

namespace App\Admin\Controllers;

use App\Models\Teacher;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

/**
 * 教师管理控制器
 * 系统管理员可以管理教师（增删改查）
 */
class TeacherController extends AdminController
{
    /**
     * 页面标题
     *
     * @var string
     */
    protected $title = '教师管理';

    /**
     * 构建列表页面
     * 使用预加载避免 N+1 问题
     * 通过权限控制：只有系统管理员角色可以访问（通过路由权限自动检查）
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new Teacher());

        $grid->model()->withCount('students');

        $grid->column('id', 'ID')->sortable();
        $grid->column('name', '姓名')->sortable()->filter('like');
        $grid->column('email', '邮箱')->sortable()->filter('like');
        $grid->column('phone', '联系电话')->filter('like');
        $grid->column('students_count', '学生数量')->display(function ($count) {
            return "<span class='badge badge-info'>{$count}</span>";
        });
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
        $teacher = Teacher::withCount('students')->findOrFail($id);
        $show = new Show($teacher);

        $show->field('id', 'ID');
        $show->field('name', '姓名');
        $show->field('email', '邮箱');
        $show->field('phone', '联系电话');
        $show->field('email_verified_at', '邮箱验证时间')->as(function ($value) {
            return $value ? $value->format('Y-m-d H:i:s') : '未验证';
        });
        $show->field('students_count', '学生数量');
        $show->field('created_at', '创建时间');
        $show->field('updated_at', '更新时间');

        // 显示关联的学生（使用预加载的关系数据）
        $show->students('学生列表', function ($students) {
            $students->resource('/admin/students');
            $students->id('ID');
            $students->name('姓名');
            $students->email('邮箱');
            $students->student_number('学号');
            $students->phone('联系电话');
            $students->created_at('创建时间');
        });

        return $show;
    }

    /**
     * 构建表单页面
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Teacher());

        $form->text('name', '姓名')->required()->rules('required|max:255');
        $form->email('email', '邮箱')->required()->rules(function ($form) {
            $rules = 'required|email';
            if (!$form->isCreating()) {
                $rules .= '|unique:teachers,email,' . $form->model()->id;
            } else {
                $rules .= '|unique:teachers,email';
            }
            return $rules;
        });
        $form->mobile('phone', '联系电话')->rules('nullable|max:20');

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

        // 保存前的回调，处理密码加密
        $form->saving(function (Form $form) {
            // 如果密码为空且是编辑模式，则不更新密码
            if (!$form->password && $form->isEditing()) {
                $form->password = $form->model()->password;
            }
        });

        return $form;
    }
}
