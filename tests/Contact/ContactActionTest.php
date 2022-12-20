<?php

namespace Tests\App\Contact;

use App\Contact\ContactAction;
use Framework\Renderer\RendererInterface;
use Framework\Response\RedirectResponse;
use Framework\Session\FlashService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Tests\ActionTestCase;

class ContactActionTest extends ActionTestCase
{
    private MockObject $renderer;
    private MockObject $flash;
    private ContactAction $action;
    private MockObject $mailer;
    private string $to = 'contact@test.com';

    public function setUp(): void
    {
        $this->renderer = $this->getMockBuilder(RendererInterface::class)->getMock();
        $this->flash = $this->getMockBuilder(FlashService::class)->disableOriginalConstructor()->getMock();
        $this->mailer = $this->createMock(MailerInterface::class);

        /** @var RendererInterface $renderer */
        $renderer = $this->renderer;
        /** @var FlashService $flash */
        $flash = $this->flash;
        /** @var Mailer $mailer */
        $mailer = $this->mailer;
        $this->action = new ContactAction($this->to, $renderer, $flash, $mailer);
    }

    public function testGet(): void
    {
        $this->renderer->expects($this->once())
            ->method('render')
            ->with('@contact/contact')
            ->willReturn('');

        call_user_func($this->action, $this->makeRequest('/contact'));
    }

    public function testPostInvalid(): void
    {
        $request = $this->makeRequest('/contact', [
            'name' => 'Valid name',
            'email' => 'email not valid',
            'content' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit'
        ]);

        $this->renderer->expects($this->once())
            ->method('render')
            ->with('@contact/contact', $this->callback(function ($params) {
                $this->assertArrayHasKey('errors', $params);
                $this->assertArrayHasKey('email', $params['errors']);
                return true;
            }))
            ->willReturn('');

        $this->flash->expects($this->once())->method('error');

        call_user_func($this->action, $request);
    }

    public function testPostValid(): void
    {
        $request = $this->makeRequest('/contact', [
            'name' => 'Valid Name',
            'email' => 'test@test.dev',
            'content' => 'Lorem ipsum dolor sit'
        ]);

        $this->flash->expects($this->once())->method('success');
        $this->mailer->expects($this->once())->method('send')->with($this->callback(function (Email $message) {
            $this->assertEquals($this->to, $message->getTo()[0]->getAddress());
            $this->assertEquals('test@test.dev', $message->getFrom()[0]->getAddress());
            $this->assertStringContainsString('tetexttextxt', $message->toString());
            $this->assertStringContainsString('hthtmlhtmlml', $message->toString());
            return true;
        }));
        $this->renderer->expects($this->any())->method('render')->willReturn('tetexttextxt', 'hthtmlhtmlml');
        $response = call_user_func($this->action, $request);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
}
