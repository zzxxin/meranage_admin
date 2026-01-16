<?php

namespace App\Admin\Controllers;

use App\Models\AdminUser;
use App\Services\TeacherService;
use Encore\Admin\Auth\Database\Role;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Hash;

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
        $grid = new Grid(new AdminUser());

        // 使用 Service 获取查询构建器，确保加载学生数量统计和角色关系
        // 只显示 user_type = 'teacher' 的用户（排除系统管理员）
        $grid->model()->where('user_type', 'teacher');

        // 加载关联数据
        $grid->model()->withCount('students')->with('roles');

        $grid->column('id', 'ID')->sortable();
        $grid->column('name', '姓名')->sortable()->filter('like');
        $grid->column('username', '用户名')->sortable()->filter('like');
        $grid->column('email', '邮箱')->sortable()->filter('like');
        $grid->column('phone', '联系电话')->filter('like');

        // 显示用户角色（系统管理员/教师）
        $grid->column('roles', '角色')->display(function ($roles) {
            if (empty($roles)) {
                return '<span class="badge badge-secondary">无角色</span>';
            }
            $badges = [];
            foreach ($roles as $role) {
                if ($role['slug'] === 'administrator') {
                    $badges[] = '<span class="badge badge-danger">系统管理员</span>';
                } elseif ($role['slug'] === 'teacher') {
                    $badges[] = '<span class="badge badge-success">教师</span>';
                } else {
                    $badges[] = '<span class="badge badge-info">' . htmlspecialchars($role['name']) . '</span>';
                }
            }
            return implode(' ', $badges);
        });

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
        $teacher = $this->teacherService->getDetailById($id)->load('roles');
        $show = new Show($teacher);

        $show->field('id', 'ID');
        $show->field('name', '姓名');
        $show->field('username', '用户名');
        $show->field('email', '邮箱');
        $show->field('phone', '联系电话');

        // 显示用户角色
        $show->field('roles', '角色')->as(function ($roles) {
            if (empty($roles)) {
                return '无角色';
            }
            $roleNames = [];
            foreach ($roles as $role) {
                if ($role['slug'] === 'administrator') {
                    $roleNames[] = '系统管理员';
                } elseif ($role['slug'] === 'teacher') {
                    $roleNames[] = '教师';
                } else {
                    $roleNames[] = $role['name'];
                }
            }
            return implode(', ', $roleNames);
        });

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
        $form = new Form(new AdminUser());

        // username 字段（用于登录，默认使用邮箱）
        $form->text('username', '用户名')
            ->required()
            ->rules(function ($form) {
                // 在编辑模式下，排除当前用户的ID
                $rule = 'required|string|max:190|unique:admin_users,username';
                if ($form->isEditing()) {
                    $rule .= ',' . $form->model()->id;
                }
                return $rule;
            })
            ->help('可用于登录教务系统用户名')
            ->default(function ($form) {
                // 如果是编辑模式，使用现有的 username；如果是创建模式且提供了 email，使用 email
                if ($form->isEditing()) {
                    return $form->model()->username;
                }
                return $form->email ?? '';
            });

        $form->text('name', '姓名')
            ->required()
            ->rules('required|string|max:255|min:1|regex:/^[\x{4e00}-\x{9fa5}a-zA-Z\s]+$/u')
            ->help('只能包含中文、英文和空格，长度1-255个字符');
        $form->email('email', '邮箱')
            ->required()
            ->rules(function ($form) use ($teacherService) {
                // 使用 Service 获取验证规则
                $excludeId = $form->isEditing() ? $form->model()->id : null;
                $rule = $teacherService->getEmailUniqueRule($excludeId);
                return $rule . '|email:rfc,dns';
            });
        $form->mobile('phone', '联系电话')
            ->rules('nullable|string|max:20|regex:/^1[3-9]\d{9}$/')
            ->help('请输入11位手机号，例如：13800138000');

        // 用户类型字段（隐藏字段，强制设置为 teacher）
        $form->hidden('user_type')->default('teacher');

        // 密码字段处理
        $form->password('password', '密码')
            ->help('留空则不修改密码，至少6个字符')
            ->rules(function ($form) use ($teacherService) {
                // 使用 Service 获取验证规则
                $rule = $teacherService->getPasswordRule($form->isCreating());
                return $rule . '|string|max:255';
            });

        // 保存前的回调，处理密码、username 和 user_type
        $form->saving(function (Form $form) {
            // 如果密码为空且是编辑模式，则不更新密码
            if (!$form->password && $form->isEditing()) {
                $form->password = $form->model()->password;
            } elseif ($form->password && !empty($form->password)) {
                // 如果密码不为空，确保加密
                // 检查密码是否已经被加密（bcrypt 哈希以 $2y$ 开头）
                if (!preg_match('/^\$2[ayb]\$.{56}$/', $form->password)) {
                    // 如果密码是明文，则加密
                    $form->password = Hash::make($form->password);
                }
            }

            // 如果 username 为空，使用 email 作为 username
            if (empty($form->username) && !empty($form->email)) {
                $form->username = $form->email;
            }

            // 如果 username 没有设置，且是编辑模式，保持原 username
            if (empty($form->username) && $form->isEditing()) {
                $form->username = $form->model()->username;
            }

            // 强制设置用户类型为教师（确保创建和更新时都是 teacher）
            $form->model()->user_type = 'teacher';
        });

        // 保存后的回调，自动分配教师角色
        $form->saved(function (Form $form) {
            $administrator = $form->model();
            $teacherRole = Role::where('slug', 'teacher')->first();

            // 如果是创建模式或用户还没有教师角色，则分配角色
            if ($teacherRole && !$administrator->roles()->where('slug', 'teacher')->exists()) {
                $administrator->roles()->attach($teacherRole->id);
            }
        });

        return $form;
    }
}
