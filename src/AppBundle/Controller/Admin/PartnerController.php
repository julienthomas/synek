<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Place;
use AppBundle\Form\PlaceType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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
     * @param Request $request
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
     * @param Request $request
     * @param Place $place
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addEditAction(Request $request, Place $place = null)
    {
        $isNew = false;
        if ($place === null) {
            $place = new Place();
            $isNew = true;
            $timezone = $this->getDoctrine()->getManager()
                ->getRepository('AppBundle:Timezone')->findOneByName('Europe/paris');
            $place->setTimezone($timezone);
        }

        $placeType = new PlaceType($this->getUser()->getLanguage(), $this->get('synek.service.shop'));
        $form      = $this->createForm($placeType, $place);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$place->getTimezone()) {
            }
        } else {
            $this->get('session')->getFlashBag()->add('error', _('Some fields are invalids.'));
        }

        return $this->render('add_edit.html.twig', [
            'form'  => $form->createView(),
            'isNew' => $isNew
        ]);
    }
}