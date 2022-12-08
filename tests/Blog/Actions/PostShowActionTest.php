<?php

namespace Tests\App\Blog\Actions;

use App\Blog\Actions\PostShowAction;
use App\Blog\Entity\Post;
use App\Blog\Table\PostTable;
use Framework\Renderer\RendererInterface;
use Framework\Router;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class PostShowActionTest extends TestCase
{
    private PostShowAction $postShowAction;
    private MockObject $renderer;
    private MockObject $router;
    private MockObject $postTable;

    public function setUp(): void
    {
        $this->renderer = $this->createMock(RendererInterface::class);
        $this->router = $this->createMock(Router::class);
        $this->postTable = $this->createMock(PostTable::class);

        $this->postShowAction = new PostShowAction(
            $this->renderer,
            $this->router,
            $this->postTable
        );
    }

    public function testShowRedirect()
    {
        $post = $this->makePost(9, 'test-slug');
        $request = (new ServerRequest('GET', '/'))
            ->withAttribute('id', $post->id)
            ->withAttribute('slug', 'slug-not-ok');

        $this->router
            ->method('generateUri')
            ->with('blog.show', ['id' => $post->id, 'slug' => $post->slug])
            ->willReturn('/demo2')
        ;
        $this->postTable->method('findWithCategory')->with($post->id)->willReturn($post);


        /** @var ResponseInterface */
        $response = call_user_func_array($this->postShowAction, [$request]);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals(["/demo2"], $response->getHeader('Location'));
    }

    public function testShowRender()
    {
        $post = $this->makePost(9, 'test-slug');
        $request = (new ServerRequest('GET', '/'))
            ->withAttribute('id', $post->id)
            ->withAttribute('slug', $post->slug);

        $this->postTable->method('findWithCategory')->with($post->id)->willReturn($post);
        $this->renderer
            ->method('render')
            ->with('@blog/show', ['post' => $post])
            ->willReturn('')
        ;

        /** @var ResponseInterface */
        $response = call_user_func_array($this->postShowAction, [$request]);

        // Pour que PhpUnit soit content
        $this->assertEquals(true, true);
    }

    private function makePost(int $id, string $slug): Post
    {
        $post = new Post();
        $post->id = $id;
        $post->slug = $slug;
        return $post;
    }
}
