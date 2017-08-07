<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class HomeController extends Controller
{
    public function homeAction()
    {
        return $this->render('home/home.html.twig');
    }


    public function getMapPlacesAction(Request $request)
    {
        $places = $this->get('synek.service.place')->getHomeMapPlaces($request->query->get('beer'));
        return new JsonResponse($places);
    }
}