<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Language;
use AppBundle\Service\BeerService;
use AppBundle\Util\DatatableUtil;
use Doctrine\ORM\EntityRepository;

class BeerRepository extends EntityRepository
{
    /**
     * @param $searchs
     * @param $order
     * @param $limit
     * @param $offset
     * @param Language $language
     * @return array
     */
    public function getDatatableList($searchs, $order, $limit, $offset, Language $language)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select(sprintf(
                'beer.id as %s, beer.name as %s, beer.alcoholDegree AS %s, type_translation.name AS %s, brewery.name AS %s',
                BeerService::DATATABLE_KEY_ID,
                BeerService::DATATABLE_KEY_NAME,
                BeerService::DATATABLE_KEY_ALCOHOL_DEGREE,
                BeerService::DATATABLE_KEY_TYPE,
                BeerService::DATATABLE_KEY_BREWERY
            ))
            ->from('AppBundle:Beer', 'beer')
            ->innerJoin('beer.type', 'type')
            ->leftJoin('type.translations', 'type_translation')
            ->leftJoin('type_translation.language', 'type_translation_language')
            ->innerJoin('beer.brewery', 'brewery')
            ->where('type_translation_language.locale = :locale')
            ->setParameter('locale', $language->getLocale());

        if ($order !== null) {
            $qb->orderBy($order['col'], $order['dir']);
        }

        if ($offset !== null && $limit !== null) {
            $qb->setFirstResult($offset);
        }
        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }
        if ($searchs !== null) {
            foreach ($searchs as $search) {
                $expr       = $search['expr'];
                $paramKey   = $search['param']['key'];
                $paramValue = $search['param']['value'];

                $qb
                    ->orHaving($expr)
                    ->setParameter($paramKey, $paramValue);
            }
        }
        return DatatableUtil::getQbData($this->_em, $qb, 'beer.id', $searchs);
    }

    /**
     * @return array
     */
    public function getBeersWithBreweries()
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('beer, brewery')
            ->from('AppBundle:Beer', 'beer')
            ->innerJoin('beer.brewery', 'brewery')
            ->orderBy('brewery.name')
            ->addOrderBy('beer.name');

        $query = $qb->getQuery();
        $query->useQueryCache(true);
        return $query->getResult();
    }
}