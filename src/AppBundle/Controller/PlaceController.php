<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Place;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PlaceController extends Controller
{
    public function informationAction(Request $request, $placeId)
    {
        $locale = $this->getUser() ? $this->getUser()->getLanguage() : $request->getLocale();
        $place  = $this->getDoctrine()->getManager()->getRepository(Place::class)
            ->getPlaceInformation($placeId, $locale);
        return $this->render('place/information.html.twig', ['place' => $place]);
    }
}