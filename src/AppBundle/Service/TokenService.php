<?php

namespace AppBundle\Service;

use AppBundle\Entity\Admin;
use AppBundle\Entity\Token;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Routing\Router;
use Symfony\Component\Translation\Translator;

class TokenService extends AbstractService
{
    /**
     * @var MailService
     */
    private $mailService;

    /**
     * @var TwigEngine
     */
    private $templating;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var array
     */
    private $tokenParameters;

    /**
     * @param EntityManager $manager
     * @param $tokenParameters
     */
    public function __construct(
        EntityManager $manager,
        MailService $mailService,
        TwigEngine $templating,
        Router $router,
        Translator $translator,
        $tokenParameters
    ) {
        parent::__construct($manager);

        $this->mailService = $mailService;
        $this->templating = $templating;
        $this->router = $router;
        $this->translator = $translator;
        $this->tokenParameters = $tokenParameters;
    }

    public function createAndSend(Admin $admin = null, User $user = null)
    {
        $token = new Token();
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $tokenPrefix = $admin ? $admin->getLogin() : $user->getEmail();
        $token
            ->setToken(hash('sha256', $tokenPrefix.uniqid(null, true)))
            ->setAdmin($admin)
            ->setUser($user)
            ->setCreatedDate($now)
            ->setTtl($this->tokenParameters['ttl']);
        $this->persistAndFlush($token);

        $subject = "[{$this->translator->trans('SYNEK partners')}] {$this->translator->trans('Reset your password')}";
        $mailBody = $this->templating->render('mail/password_reset.html.twig', ['route' => 'mabiROUTE lol']);
        $to = $admin ? $admin->getEmail() : $user->getEmail();
        $this->mailService->send($subject, $mailBody, $to);

        return $token;
    }
}
