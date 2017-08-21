<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class BeerController extends Controller
{
    /**
     * @Route("/beer-list", name="beer_list")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getBeersListAction()
    {


    }
}