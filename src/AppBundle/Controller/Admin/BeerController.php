<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Beer;
use AppBundle\Entity\Beer\Type;
use AppBundle\Entity\Brewery;
use AppBundle\Form\BeerType;
use AppBundle\Form\BreweryType;
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

        $beerForm    = $this->createForm(new BeerType($this->getUser()->getLanguage()), $beer);
        $breweryForm = $this->createForm(new BreweryType($this->getUser()->getLanguage()), new Brewery());

        return $this->render('admin/beer/add_edit.html.twig', [
            'form'        => $beerForm->createView(),
            'breweryForm' => $breweryForm->createView(),
            'isNew'       => $isNew
        ]);
    }
}