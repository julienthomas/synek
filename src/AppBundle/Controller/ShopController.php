<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Language;
use AppBundle\Entity\Place;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShopController extends Controller
{
    /**
     * @Route("/information/{id}", name="shop_information")
     * @Route("/admin/shop/information/{id}", name="admin_shop_information", defaults={"isAdmin" = true})
     * @Route("/user/shop/information", name="user_shop_information", defaults={"isUser" = true})
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function informationAction(Request $request, $id = null, $isAdmin = false, $isUser = false)
    {
        $locale   = $this->getUser() ? $this->getUser()->getLanguage()->getLocale() : $request->getLocale();
        $language = $this->getDoctrine()->getManager()->getRepository(Language::class)->findOneByLocale($locale);
        if (!$language) {
            $language = $this->getDoctrine()->getManager()->getRepository(Language::class)->findOneByLocale('fr_FR');
        }
        $shop = null;
        if ($id) {
            $shop = $this->getDoctrine()->getManager()->getRepository(Place::class)->getShopInformation($id, $language);
        } else if ($isUser) {
            $shop = $this->getUser()->getPlace();
        }
        if (!$shop) {
            throw new NotFoundHttpException();
        }
        $schedules = $this->get('synek.service.shop')->buildScheduleArray($shop->getSchedules());
        $editRoute = null;
        $layout    = 'layout.html.twig';
        if ($isAdmin) {
            $layout    = 'layout_admin.html.twig';
            $editRoute = $this->get('router')->generate('admin_shop_edit', ['id' => $id]);
        } elseif ($isUser) {
            $layout    = 'layout_user.html.twig';
            $editRoute = '#';
        }
        return $this->render('shop/information.html.twig', [
            'editRoute' => $editRoute,
            'layout'    => $layout,
            'shop'      => $shop,
            'schedules' => $schedules
        ]);
    }
}