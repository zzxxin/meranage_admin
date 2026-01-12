<?php

namespace App\Admin\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Services\StudentService;
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
     * 学生服务
     *
     * @var StudentService
     */
    protected $studentService;

    /**
     * 构造函数
     *
     * @param StudentService $studentService
     */
    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

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

        // 使用 Service 获取查询构建器
        $grid->model($this->studentService->getListQuery());

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
            // 使用 Model 方法获取教师选项
            $filter->equal('teacher_id', '所属教师')->select(Teacher::getOptionsForSelect());
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
        // 使用 Service 获取学生详情
        $student = $this->studentService->getDetailById($id);
        $show = new Show($student);

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
        $studentService = $this->studentService;
        $form = new Form(new Student());

        // 教师选择：有权限访问学生管理的用户可以选择所有教师
        // 权限控制已通过 Laravel Admin 的角色权限系统自动处理
        $form->select('teacher_id', '所属教师')
            ->options(Teacher::getOptionsForSelect())
            ->required()
            ->rules('required|exists:teachers,id');

        $form->text('name', '姓名')
            ->required()
            ->rules('required|string|max:255|min:1|regex:/^[\x{4e00}-\x{9fa5}a-zA-Z\s]+$/u')
            ->help('只能包含中文、英文和空格，长度1-255个字符');
        $form->email('email', '邮箱')
            ->required()
            ->rules(function ($form) use ($studentService) {
                // 使用 Service 获取验证规则
                $excludeId = $form->isEditing() ? $form->model()->id : null;
                $rule = $studentService->getEmailUniqueRule($excludeId);
                return $rule . '|email:rfc,dns';
            });
        $form->mobile('phone', '联系电话')
            ->rules('nullable|string|max:20|regex:/^1[3-9]\d{9}$/')
            ->help('请输入11位手机号，例如：13800138000');
        $form->text('student_number', '学号')
            ->rules(function ($form) use ($studentService) {
                // 使用 Service 获取验证规则
                $excludeId = $form->isEditing() ? $form->model()->id : null;
                $rule = $studentService->getStudentNumberUniqueRule($excludeId);
                return $rule . '|regex:/^[A-Za-z0-9_-]+$/';
            })
            ->help('只能包含字母、数字、下划线和连字符，最多50个字符');

        // 密码字段处理
        $form->password('password', '密码')
            ->help('留空则不修改密码，至少6个字符')
            ->rules(function ($form) use ($studentService) {
                // 使用 Service 获取验证规则
                $rule = $studentService->getPasswordRule($form->isCreating());
                return $rule . '|string|max:255';
            });

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
