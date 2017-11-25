<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Beer;
use AppBundle\Entity\Brewery;
use AppBundle\Entity\Place;
use AppBundle\Form\PlaceType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class ShopController extends Controller
{
    /**
     * @Route("/admin/shop", name="admin_shop")
     */
    public function listAction()
    {
        return $this->render('admin/shop/list.html.twig');
    }

    /**
     * @Route("/admin/shop/new-refresh", name="admin_shop_new_refresh")
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function newListRefreshAction(Request $request)
    {
        $data = $this->get('synek.service.shop')->getNewList($request->request->all());

        return new JsonResponse($data);
    }

    /**
     * @Route("/admin/shop/refresh", name="admin_shop_refresh")
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listRefreshAction(Request $request)
    {
        $data = $this->get('synek.service.shop')->getList($request->request->all());

        return new JsonResponse($data);
    }

    /**
     * @Route("/admin/shop/add", name="admin_shop_add")
     * @Route("/admin/shop/edit/{id}", name="admin_shop_edit")
     *
     * @param Request $request
     * @param Place   $place
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editAction(Request $request, Place $place = null)
    {
        $shopService = $this->get('synek.service.shop');
        $isNew = false;
        if (null === $place) {
            $place = $shopService->initShop();
            $isNew = true;
        }

        $basePictures = $shopService->getCurrentPictures($place);
        $formType = new PlaceType($this->getUser()->getLanguage(), $this->get('synek.service.shop'), true, true);
        $placeForm = $this->createForm($formType, $place);
        $beerForm = $this->createForm($this->get('synek.form.beer'), new Beer());
        $beerTypeForm = $this->createForm($this->get('synek.form.beer_type'), new Beer\Type());
        $breweryForm = $this->createForm($this->get('synek.form.brewery'), new Brewery());
        $breweries = $this->getDoctrine()->getManager()->getRepository(Brewery::class)->getBreweriesWithBeers();

        $placeForm->handleRequest($request);
        if ($placeForm->isSubmitted()) {
            $translator = $this->get('translator');
            $flashbag = $this->get('session')->getFlashBag();
            if ($placeForm->isValid()) {
                $shopService->saveShop($place);
                $shopService->deleteUnusedPictures($place, $basePictures);
                $flashbag->add('success', $translator->trans('Shop successfully edited.'));

                return $this->redirectToRoute('admin_shop');
            } else {
                $flashbag->add('error', $translator->trans('Some fields are invalids.'));
            }
        }

        return $this->render('admin/shop/add_edit.html.twig', [
            'isNew' => $isNew,
            'place' => $place,
            'breweries' => $breweries,
            'form' => $placeForm->createView(),
            'beerForm' => $beerForm->createView(),
            'beerTypeForm' => $beerTypeForm->createView(),
            'breweryForm' => $breweryForm->createView(),
        ]);
    }

    /**
     * @Route("/admin/shop/import", name="admin_shop_import")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Exception
     */
    public function prestashopImportAction()
    {
        $shopsNb = $this->get('synek.service.shop')->importPrestashopShops();
        $this->get('session')->getFlashBag()
            ->add('success', sprintf(_('%s shops were imported from Prestashop.'), $shopsNb));

        return $this->redirectToRoute('admin_shop');
    }

    /**
     * @Route("/admin/shop/image-upload", name="admin_shop_image_upload")
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function imageUploadAction(Request $request)
    {
        $asset = $this->get('synek.service.shop')->uploadImage($request->files->get('image'));
        if (!$asset) {
            throw new \Exception('Invalid image');
        }

        return new JsonResponse(['file' => $asset]);
    }
}
