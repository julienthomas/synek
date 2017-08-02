<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Beer;
use AppBundle\Entity\Beer\Type;
use AppBundle\Entity\Brewery;
use AppBundle\Form\BeerType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class BeerController extends Controller
{
    /**
     * @Route("/admin/beer", name="admin_beer")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $types = $this->getDoctrine()->getRepository(Type::class)
            ->getTypesWithTranslation($this->getUser()->getLanguage());
        $typesData = [];
        /** @var Type $type */
        foreach ($types as $type) {
            $typesData[$type->getId()] = $type->getTranslations()->first()->getName();
        }
        return $this->render('admin/beer/list.html.twig', ['types' => $typesData]);
    }

    /**
     * @Route("/admin/beer/refresh", name="admin_beer_refresh")
     * @param Request $request
     * @return JsonResponse
     */
    public function listRefreshAction(Request $request)
    {
        $data = $this->get('synek.service.beer')->getList($request->request->all(), $this->getUser()->getLanguage());
        return new JsonResponse($data);
    }

    /**
     * @Route("/admin/beer/add", name="admin_beer_add")
     * @Route("/admin/beer/edit/{id}", name="admin_beer_edit")
     * @param Request $request
     * @param Beer $beer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addEdit(Request $request, Beer $beer = null)
    {
        $isNew = false;
        if ($beer === null) {
            $beer = new Beer();
            $isNew = true;
        }

        $beerForm     = $this->createForm($this->get('synek.form.beer'), $beer);
        $breweryForm  = $this->createForm($this->get('synek.form.brewery'), new Brewery());
        $beerTypeForm = $this->createForm($this->get('synek.form.beer_type'), new Type());

        $beerForm->handleRequest($request);
        if ($beerForm->isSubmitted()) {
            $translator = $this->get('translator');
            if ($beerForm->isValid()) {
                $this->get('synek.service.beer')->saveBeer($beer);
                $msg = $isNew ? $translator->trans('Beer successfully added.') : $translator->trans('Beer successfully edited.');
                $this->get('session')->getFlashBag()->add('success', $msg);
                return $this->redirectToRoute('admin_beer');
            } else {
                $this->get('session')->getFlashBag()->add('error', $translator->trans('Some fields are invalids.'));
            }
        }

        return $this->render('admin/beer/add_edit.html.twig', [
            'form'         => $beerForm->createView(),
            'breweryForm'  => $breweryForm->createView(),
            'beerTypeForm' => $beerTypeForm->createView(),
            'isNew'        => $isNew
        ]);
    }
}