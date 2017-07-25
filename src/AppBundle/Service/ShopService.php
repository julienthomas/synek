<?php

namespace AppBundle\Service;

use AppBundle\Entity\Country;
use AppBundle\Entity\Place\Address;
use AppBundle\Entity\Place\Type;
use AppBundle\Entity\Place\Picture;
use AppBundle\Entity\Timezone;
use AppBundle\Util\DatatableUtil;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Place;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Acl\Exception\Exception;

class ShopService extends PlaceService
{
    const PRESTASHOP_MANAGER = 'prestashop';

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var EntityManager
     */
    private $prestashopManager;

    public function __construct(
        EntityManager $manager,
        AssetsHelper $assetsHelper,
        $placeParameters,
        \Twig_Environment $twig,
        Registry $registry
    ) {
        parent::__construct($manager, $assetsHelper, $placeParameters);
        $this->prestashopManager = $registry->getManager(self::PRESTASHOP_MANAGER);
        $this->twig              = $twig;
    }

    public function getNewList($requestData)
    {
        $listParams = $this->getListParams($requestData);

        $results = $this->manager->getRepository('AppBundle:Place')->getNewShopsDatatableList(
            $listParams['searchs'],
            $listParams['order'],
            $listParams['limit'],
            $listParams['offset']
        );

        return $this->buildDatatableData($results);
    }

    public function getList($requestData)
    {
        $listParams = $this->getListParams($requestData);

        $results = $this->manager->getRepository('AppBundle:Place')->getShopsDatatableList(
            $listParams['searchs'],
            $listParams['order'],
            $listParams['limit'],
            $listParams['offset']
        );

        return $this->buildDatatableData($results);
    }

    /**
     * @param $results
     * @return array
     */
    private function buildDatatableData($results)
    {
        $template = $this->twig->loadTemplate('admin/shop/partial/datatable_items.html.twig');
        $data     = [];
        foreach ($results['data'] as $place) {
            $data[] = [
                $place[self::DATATABLE_KEY_NAME],
                $place[self::DATATABLE_KEY_EMAIL],
                $place[self::DATATABLE_KEY_ADDRESS],
                $template->renderBlock('btns', ['id' => $place[self::DATATABLE_KEY_ID]])
            ];
        }

        return [
            'data'            => $data,
            'recordsTotal'    => $results['recordsTotal'],
            'recordsFiltered' => $results['recordsFiltered']
        ];
    }

    /**
     * @param $requestData
     * @return array
     */
    private function getListParams($requestData)
    {
        $orderColumns  = [self::DATATABLE_KEY_NAME, self::DATATABLE_KEY_EMAIL, self::DATATABLE_KEY_ADDRESS];
        $searchColumns = [
            ['name' => self::DATATABLE_KEY_NAME, 'searchType' => DatatableUtil::SEARCH_LIKE],
            ['name' => self::DATATABLE_KEY_EMAIL, 'searchType' => DatatableUtil::SEARCH_LIKE],
            ['name' => self::DATATABLE_KEY_ADDRESS, 'searchType' => DatatableUtil::SEARCH_LIKE],
        ];

        return [
            'searchs'  => DatatableUtil::getSearchs($requestData, $searchColumns),
            'order'    => DatatableUtil::getOrder($requestData, $orderColumns),
            'limit'    => DatatableUtil::getLimit($requestData),
            'offset'   => DatatableUtil::getOffset($requestData),
        ];
    }

    /**
     * @return int the number of imported shops
     * @throws \Exception
     */
    public function importPrestashopShops()
    {
        $shops = $this->getPrestashopShops();
        if (count($shops) === 0) {
            return 0;
        }
        $countriesIds = [];
        foreach ($shops as $shop) {
            $countriesIds[] = $shop['country_id'];
        }
        $entities = $this->manager->getRepository(Country::class)->findById($countriesIds);
        $countries = [];
        /** @var \AppBundle\Entity\Country $entity */
        foreach ($entities as $entity) {
            $countries[$entity->getId()] = $entity;
        }
        $shopType = $this->manager->getRepository(Type::class)->findOneByCode(Type::SHOP);
        $timezone = $this->manager->getRepository(Timezone::class)->findOneByName('Europe/Paris');
        $createdShops = [];
        $createdAddresses = [];
        foreach ($shops as $shop) {
            if (!array_key_exists($shop['country_id'], $countries)) {
                throw new \Exception("Missing country");
            }
            foreach ($shop as $key => $data) {
                if (empty($data)) {
                    $shop[$key] = null;
                }
            }
            $place = new Place();
            $address = new Address();
            $address
                ->setAddress($shop['address1'])
                ->setAddressComplement($shop['address2'])
                ->setZipCode($shop['postcode'])
                ->setCity($shop['city'])
                ->setLatitude($shop['latitude'])
                ->setLongitude($shop['longitude'])
                ->setCountry($countries[$shop['country_id']]);
            $place
                ->setType($shopType)
                ->setAddress($address)
                ->setName($shop['sign'])
                ->setEmail($shop['email'])
                ->setPhone($shop['phone'])
                ->setWebSite($shop['link'])
                ->setTimezone($timezone)
                ->setEnabled($shop['enabled'])
                ->setMycollectionplacesReferenceId($shop['id_place']);

            $createdAddresses[] = $address;
            $createdShops[] = $place;
        }
        if (count($createdAddresses) > 0) {
            $this->persistAndFlush($createdAddresses);
        }
        if (count($createdShops) > 0) {
            $this->persistAndFlush($createdShops);
        }

        return count($createdShops);
    }

    /**
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getPrestashopShops()
    {
        $shopsIds = $this->manager->getRepository(Place::class)->getShopsReferenceIds();

        $query = "
            SELECT ps_mycollectionplaces_place.id_place, ps_mycollectionplaces_place.sign,
                   ps_mycollectionplaces_place.email, ps_address.phone, ps_mycollectionplaces_place.link,
                   ps_mycollectionplaces_place.enabled,
                   ps_address.address1, ps_address.address2, ps_address.postcode, ps_address.city,
                   lamoustache_partenaires.country.id AS country_id,
                   TRIM(LEFT(ps_mycollectionplaces_place.location, LOCATE(',', ps_mycollectionplaces_place.location) - 1)) AS latitude,
                   TRIM(RIGHT(ps_mycollectionplaces_place.location, LOCATE(',', ps_mycollectionplaces_place.location) - 1)) AS longitude
            FROM ps_mycollectionplaces_place
            INNER JOIN ps_address ON ps_address.id_address = ps_mycollectionplaces_place.address
            INNER JOIN ps_country ON ps_country.id_country = ps_address.id_country
            INNER JOIN lamoustache_partenaires.country ON lamoustache_partenaires.country.iso_code = ps_country.iso_code
        ";

        $params = [];
        $paramsTypes = [];
        if (count($shopsIds) > 0) {
            $ids = [];
            foreach ($shopsIds as $id) {
                $ids[] = $id['mycollectionplacesReferenceId'];
            }
            $query .= 'WHERE ps_mycollectionplaces_place.id_place NOT IN (?)';
            $params[] = array_values($ids);
            $paramsTypes[] = Connection::PARAM_INT_ARRAY;
        }

        $query .= ' ORDER BY ps_mycollectionplaces_place.id_place';
        $stmt = $this->prestashopManager->getConnection()->executeQuery($query, $params, $paramsTypes);

        return $stmt->fetchAll();
    }

    /**
     * @param UploadedFile $file
     * @return null|string
     */
    public function uploadImage(UploadedFile $file)
    {
        $assetPath = 'assets/img/shop/picture';
        $serverPath = "{$_SERVER['DOCUMENT_ROOT']}/web/{$assetPath}";
        $mimeType = $file->getClientMimeType();

        if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
            return null;
        }
        $fileName = str_replace('.', null, uniqid('', true)) . ".{$file->getClientOriginalExtension()}";
        if (!file_exists($serverPath) || !is_dir($serverPath)) {
            try {
                mkdir($serverPath, 0755, true);
            } catch (\Exception $e) {
                throw new Exception("ShopService uploadPicture mkdir error: {$e->getMessage()}");
            }
        }
        $file->move($serverPath, $fileName);

        return "{$assetPath}/{$fileName}";
    }

    /**
     * @param $path
     * @return bool
     */
    public function verifFile($path)
    {
        $serverPath = "{$_SERVER['DOCUMENT_ROOT']}/web/{$path}";
        if (!file_exists($serverPath)) {
            return true;
        }
        $mimeType = mime_content_type($serverPath);
        return in_array($mimeType, ['image/jpeg', 'image/png']);
    }


    /**
     * @param Place $place
     */
    public function saveShop(Place $place)
    {
        $address  = $place->getAddress();
        $pictures = $place->getPictures();

        $place
            ->setAddress(null)
            ->clearPictures();

        $this->persistAndFlush($address);
        $place->setAddress($address);
        $this->persistAndFlush($place);
        $newPictures = [];
        /** @var Place\Picture $picture */
        foreach ($pictures as $picture) {
            $serverPath = "{$_SERVER['DOCUMENT_ROOT']}/web/{$picture->getFile()}";
            if ($picture->getFile() && file_exists($serverPath) && !$place->hasPicture($picture)) {
                if (count($newPictures) < 3) {
                    $picture->setPlace($place);
                    $place->addPicture($picture);
                } else {
                    $this->deleteFile($picture->getFile());
                }
            }
        }
        $this->persistAndFlush($place->getPictures()->toArray());
        $this->removeUnused($place);
    }

    /**
     * @param Place $place
     */
    private function removeUnused(Place $place)
    {
        $pictures = $this->manager->getRepository('AppBundle:Place\Picture')->findByPlace($place);

        $remove = [];
        /** @var Place\Picture $picture */
        foreach ($pictures as $picture) {
            if (!$place->hasPicture($picture)) {
                $remove[] = $picture;
                if ($picture->getFile()) {
                    $this->deleteFile($picture->getFile());
                }
            }
        }

        $this->removeAndFlush($remove);
    }

    /**
     * @param Place $place
     * @return array
     */
    public function getCurrentPictures(Place $place)
    {
        $data = [];
        /** @var Picture $picture */
        foreach ($place->getPictures() as $picture) {
            if ($picture->getFile() && file_exists($picture->getFile())) {
                $data[] = $picture->getFile();
            }
        }
        return $data;
    }

    /**
     * @param Place $place
     * @param $basePictures
     */
    public function deleteUnusedPictures(Place $place, $basePictures)
    {
        $currentPictures = $this->getCurrentPictures($place);

        foreach ($basePictures as $basePicture) {
            if (!in_array($basePicture, $currentPictures) && file_exists($basePicture)) {
                $this->deleteFile($basePicture);
            }
        }
    }

    /**
     * @param $path
     */
    private function deleteFile($path)
    {
        $serverPath = "{$_SERVER['DOCUMENT_ROOT']}/web/{$path}";
        if (file_exists($serverPath)) {
            unlink($serverPath);
        }
    }
}