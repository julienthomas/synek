<?php

namespace AppBundle\Service;

use AppBundle\Entity\Beer;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Place;
use AppBundle\Entity\Place\Type;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;

class PlaceService extends AbstractService
{
    const DATATABLE_KEY_ID = 'id';
    const DATATABLE_KEY_NAME = 'name';
    const DATATABLE_KEY_EMAIL = 'email';
    const DATATABLE_KEY_ADDRESS = 'address';

    /**
     * @var AssetsHelper
     */
    protected $assetsHelper;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var array
     */
    protected $placeParameters;

    /**
     * @param EntityManager $manager
     * @param AssetsHelper $assetsHelper
     * @param Router $router
     * @param $placeParameters
     */
    public function __construct(
        EntityManager $manager,
        AssetsHelper $assetsHelper,
        Router $router,
        $placeParameters
    ) {
        parent::__construct($manager);
        $this->assetsHelper = $assetsHelper;
        $this->router = $router;
        $this->placeParameters = $placeParameters;
    }

    /**
     * @param $beerId
     * @return array
     */
    public function getHomeMapPlaces($beerId)
    {
        $places = $this->manager->getRepository('AppBundle:Place')->getHomeMapPlaces($beerId);
        $markers = $this->placeParameters['markers'];
        $data = [];
        /** @var Place $place */
        foreach ($places as $place) {
            $address = $place->getAddress();
            $typeCode = $place->getType()->getCode();
            $marker = array_key_exists($typeCode, $markers) ? $markers[$typeCode] : $markers['DEFAULT'];
            $placeInfo = [
                'name'              => $place->getName(),
                'address'           => $address->getAddress(),
                'addressComplement' => $address->getAddressComplement(),
                'zipCode'           => $address->getZipCode(),
                'city'              => $address->getCity(),
                'latitude'          => $address->getLatitude(),
                'longitude'         => $address->getLongitude(),
                'website'           => $place->getWebsite(),
                'facebook'          => $place->getFacebook(),
                'marker'            => $this->assetsHelper->getUrl($marker)
            ];
            if ($typeCode === Type::SHOP) {
                $placeInfo += [
                    'phone'       => $place->getPhone(),
                    'email'       => $place->getEmail(),
                    'description' => $place->getDescription(),
                    'route'       => $this->router->generate('shop_information', ['id' => $place->getId()]),
                    'beers'       => $this->buildBeersArray($place->getBeers())
                ];
            }
            $data[] = $placeInfo;
        }
        return $data;
    }

    /**
     * @param $beers
     * @return array
     */
    public function buildBeersArray($beers)
    {
        $data = [];
        /** @var Beer $beer */
        foreach ($beers as $beer) {
            $data[] = [
                'name'          => $beer->getName(),
                'type'          => $beer->getType()->getTranslations()->first()->getName(),
                'alcoholDegree' => $beer->getAlcoholDegree(),
                'brewery'       => $beer->getBrewery()->getName()
            ];
        }
        return $data;
    }
}