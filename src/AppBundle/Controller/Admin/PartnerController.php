<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Place;
use AppBundle\Form\PlaceType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class PartnerController extends Controller
{
    /**
     * @Route("/admin/partner", name="admin_partner")
     */
    public function listAction()
    {
        return $this->render('admin/partner/list.html.twig');
    }

    /**
     * @Route("/admin/partner/refresh", name="admin_partner_refresh")
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listRefreshAction(Request $request)
    {
        $data = $this->get('synek.service.partner')->getList($request->request->all());

        return new JsonResponse($data);
    }

    /**
     * @Route("/admin/partner/add", name="admin_partner_add")
     * @Route("/admin/partner/edit/{id}", name="admin_partner_edit")
     *
     * @param Request $request
     * @param Place   $place
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addEditAction(Request $request, Place $place = null)
    {
        $partnerService = $this->get('synek.service.partner');
        $isNew = false;
        if (null === $place) {
            $place = $partnerService->initPartner();
            $isNew = true;
        }

        $formType = new PlaceType($this->getUser()->getLanguage());
        $form = $this->createForm($formType, $place);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $translator = $this->get('translator');
            $flashbag = $this->get('session')->getFlashBag();
            if ($form->isValid()) {
                $partnerService->savePartner($place);
                $msg = $isNew ? $translator->trans('Partner successfully added.') : $translator->trans('Partner successfully edited.');
                $flashbag->add('success', $msg);

                return $this->redirectToRoute('admin_partner');
            } else {
                $flashbag->add('error', $translator->trans('Some fields are invalids.'));
            }
        }

        return $this->render('admin/partner/add_edit.html.twig', [
            'form' => $form->createView(),
            'isNew' => $isNew,
        ]);
    }
}
