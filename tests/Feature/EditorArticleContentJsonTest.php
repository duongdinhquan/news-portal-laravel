<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleRevision;
use App\Models\Category;
use App\Models\Media;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditorArticleContentJsonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.key' => 'base64:'.base64_encode(str_repeat('k', 32)),
            'jwt.secret' => str_repeat('jwt-test-secret-', 5),
            'jwt.algo' => 'HS256',
            'jwt.issuer' => 'news-portal-test',
            'jwt.audience' => 'news-portal-test-browser',
        ]);
    }

    public function test_editor_can_update_an_article_with_json_encoded_content(): void
    {
        [$editor, $category, $article] = $this->createEditorArticle();
        $token = app(AuthService::class)->issuePair($editor)['access_token'];
        $content = [
            'type' => 'doc',
            'content' => [
                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Updated story']]],
            ],
        ];

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->put(route('editor.articles.update', $article), [
                'title' => 'Updated article title',
                'excerpt' => 'Updated excerpt',
                'category_id' => $category->id,
                'content_json' => json_encode($content, JSON_THROW_ON_ERROR),
                'content_html' => '<p>Updated story</p>',
                'cover_media_id' => '',
            ])
            ->assertRedirect(route('editor.articles.edit', $article))
            ->assertSessionHasNoErrors();

        $this->assertSame($content, $article->fresh()->workingRevision->content_json);
    }

    public function test_editor_can_create_an_article_with_json_encoded_content(): void
    {
        $editor = User::create([
            'full_name' => 'Editor Test',
            'display_name' => 'Editor Test',
            'email' => 'editor-create-json-test@example.local',
            'password' => 'Password123',
            'role' => 'EDITOR',
            'status' => 'ACTIVE',
        ]);
        $category = Category::create([
            'name' => 'Technology',
            'slug' => 'technology',
            'is_active' => true,
        ]);
        $token = app(AuthService::class)->issuePair($editor)['access_token'];
        $content = [
            'type' => 'doc',
            'content' => [
                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'New story']]],
            ],
        ];

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->post(route('editor.articles.store'), [
                'title' => 'New article title',
                'excerpt' => 'New excerpt',
                'category_id' => $category->id,
                'content_json' => json_encode($content, JSON_THROW_ON_ERROR),
                'content_html' => '<p>New story</p>',
                'cover_media_id' => '',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame($content, Article::firstOrFail()->workingRevision->content_json);
    }

    public function test_editor_update_rejects_malformed_content_json(): void
    {
        [$editor, $category, $article] = $this->createEditorArticle();
        $token = app(AuthService::class)->issuePair($editor)['access_token'];

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->put(route('editor.articles.update', $article), [
                'title' => 'Updated article title',
                'excerpt' => 'Updated excerpt',
                'category_id' => $category->id,
                'content_json' => '{invalid json',
                'content_html' => '<p>Updated story</p>',
                'cover_media_id' => '',
            ])
            ->assertSessionHasErrors('content_json');
    }

    public function test_media_urls_are_relative_to_the_current_host(): void
    {
        $media = new Media([
            'storage_disk' => 'public',
            'storage_path' => 'media/2026/11/test.jpg',
        ]);

        $this->assertSame('/storage/media/2026/11/test.jpg', $media->url);
    }

    private function createEditorArticle(): array
    {
        $editor = User::create([
            'full_name' => 'Editor Test',
            'display_name' => 'Editor Test',
            'email' => 'editor-json-test@example.local',
            'password' => 'Password123',
            'role' => 'EDITOR',
            'status' => 'ACTIVE',
        ]);
        $category = Category::create([
            'name' => 'Technology',
            'slug' => 'technology',
            'is_active' => true,
        ]);
        $article = Article::create([
            'slug' => 'editor-json-test-article',
            'author_id' => $editor->id,
            'category_id' => $category->id,
        ]);
        $revision = ArticleRevision::create([
            'article_id' => $article->id,
            'revision_number' => 1,
            'title' => 'Original article title',
            'content_json' => [],
            'content_html' => '<p>Original story</p>',
            'status' => 'DRAFT',
        ]);
        $article->update(['working_revision_id' => $revision->id]);

        return [$editor, $category, $article];
    }
}
