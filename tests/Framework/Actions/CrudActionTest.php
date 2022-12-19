<?php

namespace Tests\Framework\Actions;

use Framework\Actions\CrudAction;
use Framework\Database\Query;
use Framework\Database\Table;
use Framework\Renderer\RendererInterface;
use Framework\Router;
use Framework\Session\FlashService;
use GuzzleHttp\Psr7\ServerRequest;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CrudActionTest extends TestCase
{
    private $flash;
    private $table;

    public function setUp(): void
    {
        $this->query = $this->getMockBuilder(Query::class)->getMock();
        $this->table = $this->getMockBuilder(Table::class)->disableOriginalConstructor()->getMock();
        $this->table->method('findAll')->willReturn($this->query);
        $this->table->method('find')->willReturnCallback(function ($id) {
            $object = new \stdClass();
            $object->id = (int)$id;
            return $object;
        });
        $this->flash = $this->getMockBuilder(FlashService::class)->disableOriginalConstructor()->getMock();
        $this->renderer = $this->getMockBuilder(RendererInterface::class)->getMock();
    }

    private function makeCrudAction(): CrudAction
    {
        $this->renderer->method('render')->willReturn('');
        $router = $this->getMockBuilder(Router::class)->getMock();
        $router->method('generateUri')->willReturnCallback(fn ($url) => $url);

        /** @var Router $router */
        $action = new CrudAction($this->renderer, $router, $this->table, $this->flash);
        $property = (new ReflectionClass($action))->getProperty('viewPath');
        $property->setAccessible(true);
        $property->setValue($action, '@test');
        $property = (new ReflectionClass($action))->getProperty('acceptedParams');
        $property->setAccessible(true);
        $property->setValue($action, ['name']);
        return $action;
    }

    public function testIndex(): void
    {
        $request = new ServerRequest('GET', '/test');
        $pager = new Pagerfanta(new ArrayAdapter([1, 2]));
        $this->query->method('paginate')->willReturn($pager);
        $this->renderer
            ->expects($this->once())
            ->method('render')
            ->with('@test/index', ['items' => $pager])
        ;
        call_user_func($this->makeCrudAction(), $request);
    }

    public function testEdit(): void
    {
        $id = 3;
        $request = (new ServerRequest('GET', '/test'))->withAttribute('id', $id);
        $this->renderer
            ->expects($this->once())
            ->method('render')
            ->with(
                '@test/edit',
                $this->callback(function ($params) use ($id) {
                    $this->assertEquals($id, $params['item']->id);
                    return true;
                })
            );
        call_user_func($this->makeCrudAction(), $request);
    }

    public function testEditWithParams(): void
    {
        $id = 3;
        $request = (new ServerRequest('POST', '/test'))
            ->withAttribute('id', $id)
            ->withParsedBody(['name' => 'test']);
        $this->table
            ->expects($this->once())
            ->method('update')
            ->with($id, ['name' => 'test']);
        $response = call_user_func($this->makeCrudAction(), $request);
        $this->assertEquals(['.index'], $response->getHeader('Location'));
    }

    public function testDelete(): void
    {
        $id = 3;
        $request = (new ServerRequest('DELETE', '/test'))
            ->withAttribute('id', $id);
        $this->table
            ->expects($this->once())
            ->method('delete')
            ->with($id);
        $response = call_user_func($this->makeCrudAction(), $request);
        $this->assertEquals(['.index'], $response->getHeader('Location'));
    }

    public function testCreate(): void
    {
        $id = 3;
        $request = (new ServerRequest('GET', '/new'))->withAttribute('id', $id);
        $this->renderer
            ->expects($this->once())
            ->method('render')
            ->with(
                '@test/create',
                $this->callback(function ($params) {
                    $this->assertInstanceOf(\stdClass::class, $params['item']);
                    return true;
                })
            );
        call_user_func($this->makeCrudAction(), $request);
    }

    public function testCreateWithParams(): void
    {
        $request = (new ServerRequest('POST', '/new'))
            ->withParsedBody(['name' => 'demo']);
        $this->table
            ->expects($this->once())
            ->method('insert')
            ->with(['name' => 'demo']);
        $response = call_user_func($this->makeCrudAction(), $request);
        $this->assertEquals(['.index'], $response->getHeader('Location'));
    }
}
