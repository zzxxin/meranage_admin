<?php

namespace App\Admin\Controllers;

use App\Models\Teacher;
use App\Services\TeacherService;
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
     * 教师服务
     *
     * @var TeacherService
     */
    protected $teacherService;

    /**
     * 构造函数
     *
     * @param TeacherService $teacherService
     */
    public function __construct(TeacherService $teacherService)
    {
        $this->teacherService = $teacherService;
    }

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

        // 使用 Service 获取查询构建器，确保加载学生数量统计
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
        // 使用 Service 获取教师详情
        $teacher = $this->teacherService->getDetailById($id);
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
        $teacherService = $this->teacherService;
        $form = new Form(new Teacher());

        $form->text('name', '姓名')
            ->required()
            ->rules('required|string|max:255|min:1|regex:/^[\x{4e00}-\x{9fa5}a-zA-Z\s]+$/u')
            ->help('只能包含中文、英文和空格，长度1-255个字符')
            ->error([
                'required' => '姓名不能为空。',
                'string' => '姓名必须是字符串。',
                'max' => '姓名不能超过255个字符。',
                'min' => '姓名至少需要1个字符。',
                'regex' => '姓名只能包含中文、英文和空格。',
            ]);
        $form->email('email', '邮箱')
            ->required()
            ->rules(function ($form) use ($teacherService) {
                // 使用 Service 获取验证规则
                $excludeId = $form->isEditing() ? $form->model()->id : null;
                $rule = $teacherService->getEmailUniqueRule($excludeId);
                return $rule . '|email:rfc,dns';
            })
            ->error([
                'required' => '邮箱不能为空。',
                'email' => '请输入有效的邮箱地址。',
                'max' => '邮箱不能超过255个字符。',
                'unique' => '该邮箱已被使用。',
            ]);
        $form->mobile('phone', '联系电话')
            ->rules('nullable|string|max:20|regex:/^1[3-9]\d{9}$/')
            ->help('请输入11位手机号，例如：13800138000')
            ->error([
                'string' => '联系电话必须是字符串。',
                'max' => '联系电话不能超过20个字符。',
                'regex' => '联系电话格式不正确，请输入11位手机号。',
            ]);

        // 密码字段处理
        $form->password('password', '密码')
            ->help('留空则不修改密码，至少6个字符')
            ->rules(function ($form) use ($teacherService) {
                // 使用 Service 获取验证规则
                $rule = $teacherService->getPasswordRule($form->isCreating());
                return $rule . '|string|max:255';
            })
            ->error([
                'required' => '密码不能为空。',
                'string' => '密码必须是字符串。',
                'min' => '密码至少需要6个字符。',
                'max' => '密码不能超过255个字符。',
            ]);

        // 保存前的回调，处理密码
        $form->saving(function (Form $form) {
            // 如果密码为空且是编辑模式，则不更新密码（Model 的 casts 已自动处理密码加密）
            if (!$form->password && $form->isEditing()) {
                $form->password = $form->model()->password;
            }
        });

        return $form;
    }
}
