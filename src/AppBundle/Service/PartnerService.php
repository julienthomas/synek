<?php

namespace AppBundle\Service;

use AppBundle\Entity\Place;
use AppBundle\Util\DatatableUtil;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;

class PartnerService extends PlaceService
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @param EntityManager $manager
     * @param AssetsHelper $assetsHelper
     * @param $placeParameters
     * @param \Twig_Environment $twig
     */
    public function __construct(
        EntityManager $manager,
        AssetsHelper $assetsHelper,
        $placeParameters,
        \Twig_Environment $twig
    ) {
        parent::__construct($manager, $assetsHelper, $placeParameters);
        $this->twig = $twig;
    }

    /**
     * @param $requestData
     * @return array
     */
    public function getList($requestData)
    {
        $listParams = $this->getListParams($requestData);

        $results = $this->manager->getRepository(Place::class)->getPartnersDatatableList(
            $listParams['searchs'],
            $listParams['order'],
            $listParams['limit'],
            $listParams['offset']
        );

        $template = $this->twig->loadTemplate('admin/partner/partial/datatable_items.html.twig');
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
}