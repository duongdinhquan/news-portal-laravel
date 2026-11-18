<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicArticleVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_article_is_visible_but_pending_article_is_not(): void
    {
        $this->seed();

        $this->get('/tin/khong-gian-xanh-trong-do-thi')->assertOk();
        $this->get('/tin/du-lich-trai-nghiem-dia-phuong')->assertNotFound();
        $this->get('/tin/ban-nhap-ke-hoach-noi-dung-so')->assertNotFound();
    }

    public function test_search_does_not_expose_drafts_or_pending_reviews(): void
    {
        $this->seed();

        $this->get('/tim-kiem?q=Kế hoạch nội dung số')
            ->assertOk()
            ->assertDontSee('Bản nháp: Kế hoạch nội dung số cho cộng đồng');

        $this->get('/tim-kiem?q=không gian xanh')
            ->assertOk()
            ->assertSee('Không gian xanh trong đô thị');
    }
}
