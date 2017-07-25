<?php

namespace AppBundle\Service;

use AppBundle\Entity\Beer;
use AppBundle\Entity\Language;
use AppBundle\Util\DatatableUtil;
use Doctrine\ORM\EntityManager;

class BeerService extends AbstractService
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    const DATATABLE_KEY_ID             = 'id';
    const DATATABLE_KEY_NAME           = 'name';
    const DATATABLE_KEY_TYPE           = 'type_name';
    const DATATABLE_KEY_ALCOHOL_DEGREE = 'alcohol_degree';
    const DATATABLE_KEY_BREWERY        = 'address';

    /**
     * @param EntityManager $manager
     * @param \Twig_Environment $twig
     */
    public function __construct(EntityManager $manager, \Twig_Environment $twig)
    {
        parent::__construct($manager);
        $this->twig = $twig;
    }

    /**
     * @param $requestData
     * @param Language $language
     * @return array
     */
    public function getList($requestData, Language $language)
    {
        $listParams = $this->getListParams($requestData);

        $results = $this->manager->getRepository(Beer::class)->getDatatableList(
            $listParams['searchs'],
            $listParams['order'],
            $listParams['limit'],
            $listParams['offset'],
            $language
        );

        $template = $this->twig->loadTemplate('admin/beer/partial/datatable_items.html.twig');
        $data     = [];
        foreach ($results['data'] as $place) {
            $data[] = [
                $place[self::DATATABLE_KEY_NAME],
                $place[self::DATATABLE_KEY_TYPE],
                $place[self::DATATABLE_KEY_ALCOHOL_DEGREE],
                $place[self::DATATABLE_KEY_BREWERY],
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
    protected function getListParams($requestData)
    {
        $orderColumns  = [self::DATATABLE_KEY_NAME, self::DATATABLE_KEY_TYPE, self::DATATABLE_KEY_ALCOHOL_DEGREE, self::DATATABLE_KEY_BREWERY];
        $searchColumns = [
            ['name' => self::DATATABLE_KEY_NAME, 'searchType' => DatatableUtil::SEARCH_LIKE],
            ['name' => self::DATATABLE_KEY_TYPE, 'searchType' => DatatableUtil::SEARCH_EQUAL],
            ['name' => self::DATATABLE_KEY_ALCOHOL_DEGREE, 'searchType' => DatatableUtil::SEARCH_LIKE],
            ['name' => self::DATATABLE_KEY_BREWERY, 'searchType' => DatatableUtil::SEARCH_LIKE]
        ];

        return [
            'searchs'  => DatatableUtil::getSearchs($requestData, $searchColumns),
            'order'    => DatatableUtil::getOrder($requestData, $orderColumns),
            'limit'    => DatatableUtil::getLimit($requestData),
            'offset'   => DatatableUtil::getOffset($requestData),
        ];
    }
}