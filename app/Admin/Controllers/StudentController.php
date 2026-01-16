<?php

namespace App\Admin\Controllers;

use App\Models\AdminUser;
use App\Models\Student;
use App\Services\StudentService;
use App\Services\TeacherService;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
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
     * 教师服务
     *
     * @var TeacherService
     */
    protected $teacherService;

    /**
     * 构造函数
     *
     * @param StudentService $studentService
     * @param TeacherService $teacherService
     */
    public function __construct(StudentService $studentService, TeacherService $teacherService)
    {
        $this->studentService = $studentService;
        $this->teacherService = $teacherService;
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

        // 获取当前登录用户
        $authUser = Admin::user();

        // 从数据库重新加载用户，确保获取最新的属性（包括 user_type）
        $user = null;
        if ($authUser) {
            $user = AdminUser::find($authUser->id);
        }

        // 先设置查询条件和关联加载（在设置列之前）
        $grid->model()->with('teacher');
        
        // 如果是教师，只显示自己创建的学生
        if ($user && isset($user->user_type) && $user->user_type === 'teacher') {
            $grid->model()->where('teacher_id', $user->id);
        }

        // 设置列
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
        $grid->filter(function ($filter) use ($user) {
            $filter->like('name', '姓名');
            $filter->like('email', '邮箱');
            $filter->like('phone', '联系电话');
            $filter->like('student_number', '学号');

            // 如果是教师，不显示教师过滤器（因为只能看到自己的学生）
            // 如果是系统管理员，可以按教师过滤
            if (!$user || $user->user_type !== 'teacher') {
                // 使用 Service 方法获取教师选项（只显示拥有"教师"角色的用户）
                $filter->equal('teacher_id', '所属教师')->select($this->teacherService->getOptionsForSelect());
            }

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
        // 获取当前登录用户
        $user = Admin::user();

        // 使用 Service 获取学生详情（如果是教师，会验证是否拥有该学生）
        $student = $this->studentService->getDetailById($id, $user);
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

        // 获取当前登录用户
        $currentUser = Admin::user();

        // 教师选择：系统管理员可以选择所有教师，教师只能选择自己
        if ($currentUser && $currentUser->user_type === 'teacher') {
            // 教师只能选择自己作为所属教师
            $form->select('teacher_id', '所属教师')
                ->options([$currentUser->id => $currentUser->name])
                ->default($currentUser->id)
                ->required()
                ->rules('required|exists:admin_users,id')
                ->readOnly(); // 教师不能修改所属教师
        } else {
            // 系统管理员可以选择所有教师
            $form->select('teacher_id', '所属教师')
                ->options($this->teacherService->getOptionsForSelect())
                ->required()
                ->rules('required|exists:admin_users,id');
        }

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

        // 保存前的回调，处理密码和验证
        $form->saving(function (Form $form) use ($currentUser) {
            // 如果密码为空且是编辑模式，则不更新密码（Model 的 casts 已自动处理密码加密）
            if (!$form->password && $form->isEditing()) {
                $form->password = $form->model()->password;
            }

            // 如果是教师，强制设置 teacher_id 为当前用户
            if ($currentUser && $currentUser->user_type === 'teacher') {
                $form->teacher_id = $currentUser->id;

                // 如果是编辑模式，验证教师是否拥有该学生
                if ($form->isEditing() && $form->model()->teacher_id != $currentUser->id) {
                    return back()->withErrors(['teacher_id' => '您无权修改其他教师的学生信息。'])->withInput();
                }
            }

            // 创建时检查学生是否已经被其他教师添加
            if ($form->isCreating()) {
                // 通过邮箱或学号检查学生是否已存在
                $existingStudent = null;
                if (!empty($form->email)) {
                    $existingStudent = Student::where('email', $form->email)->first();
                }
                if (!$existingStudent && !empty($form->student_number)) {
                    $existingStudent = Student::where('student_number', $form->student_number)->first();
                }

                if ($existingStudent) {
                    // 如果学生已存在，检查是否属于其他教师
                    $expectedTeacherId = $currentUser && $currentUser->user_type === 'teacher'
                        ? $currentUser->id
                        : $form->teacher_id;

                    if ($existingStudent->teacher_id != $expectedTeacherId) {
                        $teacher = $existingStudent->teacher;
                        $teacherName = $teacher ? $teacher->name : '未知教师';

                        $errorMessage = '该学生已被教师"' . $teacherName . '"添加，无法重复添加。';
                        if (!empty($form->email) && $existingStudent->email === $form->email) {
                            $errorMessage .= '（邮箱：' . $form->email . '）';
                        }
                        if (!empty($form->student_number) && $existingStudent->student_number === $form->student_number) {
                            $errorMessage .= '（学号：' . $form->student_number . '）';
                        }

                        return back()->withErrors(['email' => $errorMessage])->withInput();
                    }
                }
            }
        });

        return $form;
    }
}
