<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Language;
use AppBundle\Service\BreweryService;
use AppBundle\Util\DatatableUtil;
use Doctrine\ORM\EntityRepository;

class BreweryRepository extends EntityRepository
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
                'brewery.id as %s, brewery.name as %s, country.id as %s, translations.name as %s, count(beers) as %s',
                BreweryService::DATATABLE_KEY_ID,
                BreweryService::DATATABLE_KEY_NAME,
                BreweryService::DATATABLE_KEY_COUNTRY_ID,
                BreweryService::DATATABLE_KEY_COUNTRY_NAME,
                BreweryService::DATATABLE_KEY_BEER_NUMBER
            ))
            ->from('AppBundle:Brewery', 'brewery')
            ->innerJoin('brewery.country', 'country')
            ->innerJoin('country.translations', 'translations')
            ->leftJoin('brewery.beers', 'beers')
            ->where('translations.language = :language')
            ->groupBy('brewery.id')
            ->setParameter('language', $language);

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
                $expr = $search['expr'];
                $paramKey = $search['param']['key'];
                $paramValue = $search['param']['value'];

                $qb
                    ->orHaving($expr)
                    ->setParameter($paramKey, $paramValue);
            }
        }

        return DatatableUtil::getQbData($this->_em, $qb, 'brewery.id', $searchs);
    }

//    /**
//     * @param Type $type
//     * @return array
//     */
//    public function getTypeWithTranslations(Type $type)
//    {
//        $qb = $this->_em->createQueryBuilder();
//        $qb
//            ->select('type, translation')
//            ->from('AppBundle:Beer\Type', 'type')
//            ->innerJoin('type.translations', 'translation')
//            ->innerJoin('translation.language', 'language')
//            ->where('type = :type')
//            ->setParameter('type', $type);
//
//        $query = $qb->getQuery();
//        $query->useQueryCache(true);
//        return $query->getResult();
//    }
//
//    /**
//     * @param $language
//     * @return array
//     */
//    public function getTypesWithTranslation(Language $language)
//    {
//        $qb = $this->_em->createQueryBuilder();
//        $qb
//            ->select('type, translations')
//            ->from('AppBundle:Beer\Type', 'type')
//            ->innerJoin('type.translations', 'translations')
//            ->where('translations.language = :language')
//            ->orderBy('translations.name')
//            ->setParameter('language', $language);
//
//        $query = $qb->getQuery();
//        $query->useQueryCache(true);
//        return $query->getResult();
//    }
}