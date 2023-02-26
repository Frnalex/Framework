<?php

namespace Tests\App\Account\Actions;

use App\Account\Actions\AccountEditAction;
use App\Account\User;
use App\Auth\UserTable;
use Framework\Auth;
use Framework\Renderer\RendererInterface;
use Framework\Session\FlashService;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\ActionTestCase;

class AccountEditActionTest extends ActionTestCase
{
    private MockObject $userTable;
    private MockObject $renderer;
    private MockObject $auth;
    private MockObject $flashService;
    private AccountEditAction $action;
    private User $user;

    public function setUp(): void
    {
        // UserTable
        $this->userTable = $this->getMockBuilder(UserTable::class)->disableOriginalConstructor()->getMock();

        // Renderer
        $this->renderer = $this->createMock(RendererInterface::class);

        // User
        $this->user = new User();
        $this->user->id = 3;

        // Auth
        $this->auth = $this->getMockBuilder(Auth::class)->getMock();
        $this->auth->method('getUser')->willReturn($this->user);

        // Flash
        $this->flashService = $this->getMockBuilder(FlashService::class)->disableOriginalConstructor()->getMock();

        /** @var UserTable $userTable */
        $userTable = $this->userTable;
        /** @var Auth $auth */
        $auth = $this->auth;
        /** @var FlashService $flashService */
        $flashService = $this->flashService;
        $this->action = new AccountEditAction(
            $this->renderer,
            $auth,
            $flashService,
            $userTable
        );
    }

    public function testValid(): void
    {
        $this->userTable->expects($this->once())->method('update')->with(3, [
            'firstname' => 'John',
            'lastname' => 'Doe'
        ]);
        $response = call_user_func($this->action, $this->makeRequest('/test', [
            'firstname' => 'John',
            'lastname' => 'Doe',
        ]));

        $this->assertRedirect($response, '/test');
    }

    public function testValidWithPassword(): void
    {
        $this->userTable->expects($this->once())
            ->method('update')
            ->with(3, $this->callback(function (array $params) {
                $this->assertEquals(['firstname', 'lastname', 'password'], array_keys($params));
                return true;
            }));

        $response = call_user_func($this->action, $this->makeRequest('/test', [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'password' => 'password',
            'password_confirm' => 'password',

        ]));

        $this->assertRedirect($response, '/test');
    }

    public function testPostInvalid(): void
    {
        $this->userTable->expects($this->never())->method('update');
        $this->renderer->expects($this->once())
            ->method('render')
            ->with('@account/account', $this->callback(function (array $params) {
                $this->assertEquals(['password'], array_keys($params['errors']));
                return true;
            }))
            ->willReturn('');

        call_user_func($this->action, $this->makeRequest('/test', [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'password' => 'password',
            'password_confirm' => 'not-same-password',

        ]));
    }
}
