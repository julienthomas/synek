<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Place;
use AppBundle\Util\DatatableUtil;
use AppBundle\Entity\Place\Type;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;

class PlaceService extends AbstractService
{
    const DATATABLE_KEY_ID      = 'id';
    const DATATABLE_KEY_NAME    = 'name';
    const DATATABLE_KEY_EMAIL   = 'email';
    const DATATABLE_KEY_ADDRESS = 'address';

    /**
     * @var AssetsHelper
     */
    protected $assetsHelper;

    /**
     * @var array
     */
    protected $placeParameters;

    /**
     * @param EntityManager $manager
     * @param AssetsHelper $assetsHelper
     * @param $placeParameters
     */
    public function __construct(
        EntityManager $manager,
        AssetsHelper $assetsHelper,
        $placeParameters
    ) {
        parent::__construct($manager);
        $this->assetsHelper    = $assetsHelper;
        $this->placeParameters = $placeParameters;
    }

    /**
     * @param $name
     * @param $beers
     * @return array
     */
    public function getHomeMapPlaces($name, $beers)
    {
        $places    = $this->manager->getRepository('AppBundle:Place')->getHomeMapPlaces($name, $beers);
        $markers   = $this->placeParameters['markers'];
        $data      = [];
        /** @var Place $place */
        foreach ($places as $place) {
            $address   = $place->getAddress();
            $typeCode  = $place->getType()->getCode();
            $marker    = array_key_exists($typeCode, $markers) ? $markers[$typeCode] : $markers['DEFAULT'];
            $placeInfo = [
                'name'              => $place->getName(),
                'address'           => $address->getAddress(),
                'addressComplement' => $address->getAddressComplement(),
                'zipCode'           => $address->getZipCode(),
                'city'              => $address->getCity(),
                'latitude'          => $address->getLatitude(),
                'longitude'         => $address->getLongitude(),
                'marker'            => $this->assetsHelper->getUrl($marker),
            ];

            if ($typeCode === Type::SHOP) {
                $placeInfo += [
                    'phone'       => $place->getPhone(),
                    'description' => $place->getDescription(),
                    'beers'       => $place->getBeers(),
                    'pictures'    => $place->getPictures(),
                    'events'      => $place->getEvents()
                ];
            }
            $data[] = $placeInfo;
        }
        return $data;
    }
}