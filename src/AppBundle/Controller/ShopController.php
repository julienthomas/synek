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
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function informationAction(Request $request, $id)
    {
        $locale   = $this->getUser() ? $this->getUser()->getLanguage()->getLocale() : $request->getLocale();
        $language = $this->getDoctrine()->getManager()->getRepository(Language::class)->findOneByLocale($locale);
        if (!$language) {
            $language = $this->getDoctrine()->getManager()->getRepository(Language::class)->findOneByLocale('fr_FR');
        }
        $shop = $this->getDoctrine()->getManager()->getRepository(Place::class)->getShopInformation($id, $language);
        if (!$shop) {
            throw new NotFoundHttpException();
        }
        $schedules = $this->get('synek.service.shop')->buildScheduleArray($shop->getSchedules());
        return $this->render('shop/information.html.twig', [
            'shop'      => $shop,
            'schedules' => $schedules
        ]);
    }
}