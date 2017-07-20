<?php

namespace AppBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class HomeController extends Controller
{
    /**
     * @Route("/admin", name="admin_home")
     */
    public function homeAction()
    {
        return $this->render('admin/home/home.html.twig');
    }
}