<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class HomeController extends Controller
{
    const SESSION_HONE_SELECTED_BEER = 'home_selected_beer';

    /**
     * @Route("/", name="home")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function homeAction()
    {
        $this->get('synek.service.token')->createAndSend($this->getUser());
        $list = $this->get('synek.service.beer')->getBeerList();
        $selectedBeer = $this->get('session')->get(self::SESSION_HONE_SELECTED_BEER);

        return $this->render('home/home.html.twig', [
            'beerList' => $list,
            'selectedBeer' => $selectedBeer,
        ]);
    }

    /**
     * @Route("/places", name="home_places")
     * @Method("GET")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getMapPlacesAction(Request $request)
    {
        $selectedBeer = $request->query->get('beer');
        $this->get('session')->set(self::SESSION_HONE_SELECTED_BEER, $selectedBeer);
        $places = $this->get('synek.service.place')->getHomeMapPlaces($selectedBeer);

        return new JsonResponse($places);
    }
}
