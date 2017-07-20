<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class HomeController extends Controller
{
    /**
     *
     */

    /**
     * @Route("/", name="home")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function homeAction()
    {
        $places = $this->get('synek.service.place')->getHomeMapPlaces(null, null);
        return $this->render('home/home.html.twig', ['places' => $places]);
    }
}