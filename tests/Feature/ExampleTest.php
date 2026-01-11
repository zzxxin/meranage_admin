<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * 测试根路径重定向到管理后台
     */
    public function test_the_application_redirects_to_admin(): void
    {
        $response = $this->get('/');

        // 根路径应该重定向到 /admin
        $response->assertRedirect('/admin');
    }
}
