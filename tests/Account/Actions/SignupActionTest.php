<?php

namespace Tests\App\Account\Actions;

use App\Account\Actions\SignupAction;
use App\Auth\DatabaseAuth;
use App\Auth\User;
use App\Auth\UserTable;
use Framework\Renderer\RendererInterface;
use Framework\Router;
use Framework\Session\FlashService;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\ActionTestCase;

class SignupActionTest extends ActionTestCase
{
    private MockObject $userTable;
    private MockObject $renderer;
    private MockObject $router;
    private MockObject $auth;
    private MockObject $flashService;
    private SignupAction $action;

    public function setUp(): void
    {
        // UserTable
        $this->userTable = $this->getMockBuilder(UserTable::class)->disableOriginalConstructor()->getMock();
        $pdo = $this->getMockBuilder(PDO::class)->disableOriginalConstructor()->getMock();
        $statement = $this->getMockBuilder(PDOStatement::class)->getMock();
        $statement->expects($this->any())->method('fetchColumn')->willReturn(false);
        $pdo->method('prepare')->willReturn($statement);
        $pdo->method('lastInsertId')->willReturn('3');
        $this->userTable->method('getTable')->willReturn('fake');
        $this->userTable->method('getPdo')->willReturn($pdo);

        // Renderer
        $this->renderer = $this->createMock(RendererInterface::class);

        // Router
        $this->router = $this->getMockBuilder(Router::class)->getMock();
        $this->router->method('generateUri')->willReturnCallback(fn ($args) => $args);

        // Auth
        $this->auth = $this->getMockBuilder(DatabaseAuth::class)->disableOriginalConstructor()->getMock();

        // Flash
        $this->flashService = $this->getMockBuilder(FlashService::class)->disableOriginalConstructor()->getMock();

        /** @var UserTable $userTable */
        $userTable = $this->userTable;
        /** @var Router $router */
        $router = $this->router;
        /** @var DatabaseAuth $auth */
        $auth = $this->auth;
        /** @var FlashService $flashService */
        $flashService = $this->flashService;
        $this->action = new SignupAction($this->renderer, $userTable, $router, $auth, $flashService);
    }

    public function testGet(): void
    {
        $this->renderer->expects($this->once())
            ->method('render')
            ->with('@account/signup');
        call_user_func($this->action, $this->makeRequest());
    }

    public function testPostInvalid(): void
    {
        $this->renderer->expects($this->once())
            ->method('render')
            ->with('@account/signup', $this->callback(function (array $params) {
                $this->assertArrayHasKey('errors', $params);
                $this->assertEquals(['email', 'password'], array_keys($params['errors']));
                return true;
            }))
            ->willReturn('');

        call_user_func($this->action, $this->makeRequest('/test', [
            'username' => 'Test username',
            'email' => 'email not valid',
            'password' => 'password',
            'password_confirm' => 'wrongpassword'
        ]));
    }

    public function testPostWithNoPassword(): void
    {
        $this->renderer->expects($this->once())
            ->method('render')
            ->with('@account/signup', $this->callback(function (array $params) {
                $this->assertArrayHasKey('errors', $params);
                $this->assertEquals(['email', 'password'], array_keys($params['errors']));
                return true;
            }))
            ->willReturn('');

        call_user_func($this->action, $this->makeRequest('/test', [
            'username' => 'Test username',
            'email' => 'email not valid',
            'password' => '',
            'password_confirm' => ''
        ]));
    }

    public function testPostValid(): void
    {
        $this->renderer->expects($this->never())->method('render');
        $this->userTable->expects($this->once())
            ->method('insert')
            ->with($this->callback(function (array $userParams) {
                $this->assertEquals('Test username', $userParams['username']);
                $this->assertEquals('test@test.dev', $userParams['email']);
                $this->assertTrue(password_verify('password', $userParams['password']));
                return true;
            }));
        $this->auth->expects($this->once())
            ->method('setUser')
            ->with($this->callback(function (User $user) {
                $this->assertEquals('Test username', $user->username);
                $this->assertEquals('test@test.dev', $user->email);
                $this->assertEquals(3, $user->id);
                return true;
            }));
        $this->flashService->expects($this->once())->method('success')->with('Votre compte a bien été créé');

        $response = call_user_func($this->action, $this->makeRequest('/test', [
            'username' => 'Test username',
            'email' => 'test@test.dev',
            'password' => 'password',
            'password_confirm' => 'password'
        ]));

        $this->assertRedirect($response, 'account');
    }
}
