<?php

namespace AppBundle\Service;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

class MailService
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var array
     */
    private $mailParameters;

    /**
     * @param \Swift_Mailer $mailer
     * @param KernelInterface $kernel
     * @param $mailParameters
     */
    public function __construct(\Swift_Mailer $mailer, KernelInterface $kernel, $mailParameters)
    {
        $this->mailer         = $mailer;
        $this->kernel         = $kernel;
        $this->mailParameters = $mailParameters;
    }

    /**
     * @param $subject
     * @param $body
     * @param $to
     * @param string $bodyType
     * @param null $from
     */
    public function send($subject, $body, $to, $bodyType = 'text/html', $from = null)
    {
        if ($this->kernel->getEnvironment() === 'dev') {
            $from = 'julien.thomas0@gmail.com';
            $to   = 'julien.thomas0@gmail.com';
        } else {
            $from = $from ?: $this->mailParameters['from'];
        }

        $message = new \Swift_Message();
        $message
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($body, $bodyType);
        $this->mailer->send($message);
    }
}