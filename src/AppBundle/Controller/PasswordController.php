<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PasswordController extends Controller
{
    /**
     * @Route("/forgotten-password", name="password_forgotten")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function passwordForgottenAction(Request $request)
    {
        $passwordService = $this->get('synek.service.password');
        $tokenService = $this->get('synek.service.token');
        $form = $passwordService->getForgottenForm();

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $translator = $this->get('translator');
            $flashbag = $this->get('session')->getFlashBag();
            if ($form->isValid()) {
                if ($tokenService->createAndSend($form->get('login')->getData())) {
                    $flashbag->add(
                        'success',
                        $translator->trans('An email will be send to your address in a few moment.')
                    );

                    return $this->redirectToRoute('home');
                }
                $flashbag->add('error', $translator->trans("This login doesn't match any account."));
            } else {
                $flashbag->add('error', $translator->trans('Some fields are invalids.'));
            }
        }

        return $this->render('password_forgotten.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/reset-password/{token}", name="password_reset")
     *
     * @param string $token
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function passwordResetAction(Request $request, $token)
    {
        $passwordService = $this->get('synek.service.password');
        $token = $this->get('synek.service.token')->findToken($token);
        if (null === $token) {
            $msg = $this->get('translator')->trans('This token is not valid.');
            $this->get('session')->getFlashBag()->add('error', $msg);

            return $this->redirectToRoute('home');
        }

        $form = $passwordService->getResetForm();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $translator = $this->get('translator');
            $flashbag = $this->get('session')->getFlashBag();
            if ($form->isValid()) {
                $passwordService->setPasswordFromToken($token, $form->get('password')->getData());
                $flashbag->add('success', $translator->trans('Your password has been changed.'));

                return $this->redirectToRoute('home');
            }
            $flashbag->add('error', $translator->trans('Some fields are invalids.'));
        }

        return $this->render('password_reset.html.twig', ['form' => $form->createView()]);
    }
}
