<?php

namespace App\Contact;

use Framework\Renderer\RendererInterface;
use Framework\Response\RedirectResponse;
use Framework\Session\FlashService;
use Framework\Validator;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactAction
{
    public function __construct(
        private string $to,
        private RendererInterface $renderer,
        private FlashService $flashService,
        private MailerInterface $mailer
    ) {
    }

    public function __invoke(ServerRequestInterface $request): string|RedirectResponse
    {
        if ($request->getMethod() === 'GET') {
            return  $this->renderer->render('@contact/contact');
        }

        $params = $request->getParsedBody();
        $validator = new Validator($params);
        $validator->required('name', 'email', 'content')
            ->length('name', 5)
            ->email('email')
            ->length('content', 5)
        ;

        if ($validator->isValid()) {
            $this->flashService->success('Merci pour votre email');
            $message = (new Email())
                ->from($params['email'])
                ->to($this->to)
                ->subject('Formulaire de contact')
                ->text($this->renderer->render('@contact/email/contact.text', $params))
                ->html($this->renderer->render('@contact/email/contact.html', $params))

            ;
            $this->mailer->send($message);
            return new RedirectResponse($request->getUri());
        } else {
            $this->flashService->error('Merci de corriger vos erreurs');
            $errors = $validator->getErrors();
            return $this->renderer->render('@contact/contact', ['errors' => $errors]);
        }
    }
}
