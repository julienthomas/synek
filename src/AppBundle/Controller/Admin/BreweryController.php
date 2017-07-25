<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Brewery;
use AppBundle\Form\BreweryType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class BreweryController extends Controller
{
    /**
     * @Route("/admin/brewery", name="admin_brewery")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        return $this->render('admin/brewery/list.html.twig');
    }

    /**
     * @Route("/admin/brewery/refresh", name="admin_brewery_refresh")
     * @param Request $request
     * @return JsonResponse
     */
    public function listRefreshAction(Request $request)
    {
        $data = $this->get('synek.service.brewery')->getList($request->request->all());
        return new JsonResponse($data);
    }

    /**
     * @Route("/admin/brewery/add", name="admin_brewery_add")
     * @Route("/admin/brewery/edit/{id}", name="admin_brewery_edit")
     * @param Request $request
     * @param Brewery $brewery
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addEdit(Request $request, Brewery $brewery = null)
    {
        $isNew = false;
        if ($brewery === null) {
            $type = new Brewery();
            $isNew = true;
        }

        $form = $this->createForm(new BreweryType($this->getUser()->getLanguage()), $brewery);
//        $form->handleRequest($request);
//        if ($form->isSubmitted()) {
//            $translator = $this->get('translator');
//            if ($form->isValid()) {
//                $this->get('synek.service.beer_type')->saveType($type);
//                $msg = $isNew ? $translator->trans('Type successfully added.') : $translator->trans('Type successfully edited.');
//                $this->get('session')->getFlashBag()->add('success', $msg);
//                return $this->redirectToRoute('admin_beer_type');
//            } else {
//                $this->get('session')->getFlashBag()->add('error', $translator->trans('Some fields are invalids.'));
//            }
//        }
        return $this->render('admin/brewery/add_edit.html.twig', [
            'form'  => $form->createView(),
            'isNew' => $isNew
        ]);
    }

//    /**
//     * @Route("/admin/beer-type/import", name="admin_beer_type_import")
//     * @param Request $request
//     * @return \Symfony\Component\HttpFoundation\Response
//     */
//    public function importAction(Request $request)
//    {
//        return $this->render('admin/beer_type/import.html.twig');
//    }
//
//    /**
//     * @Route("/admin/beer-type/import/process", name="admin_beer_type_import_process")
//     * @param Request $request
//     * @return \Symfony\Component\HttpFoundation\Response
//     */
//    public function importProcessAction(Request $request)
//    {
//        $beerTypeService = $this->get('synek.service.beer_type');
//        $file = $request->files->get('file');
//        if (($types = $beerTypeService->parseFile($file)) === false) {
//            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
//        }
//        $data = $beerTypeService->importTypes($types);
//        return new JsonResponse($data, Response::HTTP_CREATED);
//    }
}