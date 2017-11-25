<?php

namespace AppBundle\Service;

use AppBundle\Entity\Place;
use AppBundle\Entity\Place\Type;
use AppBundle\Entity\Timezone;
use AppBundle\Util\DatatableUtil;
use Doctrine\ORM\EntityManager;

class PartnerService extends AbstractService
{
    const DATATABLE_KEY_ID = 'id';
    const DATATABLE_KEY_NAME = 'name';
    const DATATABLE_KEY_EMAIL = 'email';
    const DATATABLE_KEY_ADDRESS = 'address';

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @param EntityManager     $manager
     * @param \Twig_Environment $twig
     */
    public function __construct(EntityManager $manager, \Twig_Environment $twig)
    {
        parent::__construct($manager);
        $this->twig = $twig;
    }

    /**
     * @param $requestData
     *
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

        $template = $this->twig->loadTemplate('admin/partner/datatable/items.html.twig');
        $data = [];
        foreach ($results['data'] as $place) {
            $data[] = [
                $place[self::DATATABLE_KEY_NAME],
                $place[self::DATATABLE_KEY_EMAIL],
                $place[self::DATATABLE_KEY_ADDRESS],
                $template->renderBlock('btns', ['id' => $place[self::DATATABLE_KEY_ID]]),
            ];
        }

        return [
            'data' => $data,
            'recordsTotal' => $results['recordsTotal'],
            'recordsFiltered' => $results['recordsFiltered'],
        ];
    }

    /**
     * @param $requestData
     *
     * @return array
     */
    private function getListParams($requestData)
    {
        $orderColumns = [self::DATATABLE_KEY_NAME, self::DATATABLE_KEY_EMAIL, self::DATATABLE_KEY_ADDRESS];
        $searchColumns = [
            ['name' => self::DATATABLE_KEY_NAME, 'searchType' => DatatableUtil::SEARCH_LIKE],
            ['name' => self::DATATABLE_KEY_EMAIL, 'searchType' => DatatableUtil::SEARCH_LIKE],
            ['name' => self::DATATABLE_KEY_ADDRESS, 'searchType' => DatatableUtil::SEARCH_LIKE],
        ];

        return [
            'searchs' => DatatableUtil::getSearchs($requestData, $searchColumns),
            'order' => DatatableUtil::getOrder($requestData, $orderColumns),
            'limit' => DatatableUtil::getLimit($requestData),
            'offset' => DatatableUtil::getOffset($requestData),
        ];
    }

    /**
     * @return Place
     */
    public function initPartner()
    {
        $place = new Place();
        $type = $this->manager->getRepository(Type::class)->findOneByCode(Type::PARTNER);
        $timezone = $this->manager->getRepository(Timezone::class)->findOneByName('Europe/paris');
        $place
            ->setTimezone($timezone)
            ->setEnabled(true)
            ->setType($type);

        return $place;
    }

    /**
     * @param Place $place
     */
    public function savePartner(Place $place)
    {
        $this->persistAndFlush([$place->getAddress(), $place]);
    }
}
