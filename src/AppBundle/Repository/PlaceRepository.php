<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Place\Type;
use AppBundle\Service\PlaceService;
use AppBundle\Util\DatatableUtil;
use Doctrine\ORM\EntityRepository;

class PlaceRepository extends EntityRepository
{
    /**
     * @param $searchs
     * @param $order
     * @param $limit
     * @param $offset
     * @return array
     */
    public function getNewShopsDatatableList($searchs, $order, $limit, $offset)
    {
        $qb = $this->getPlacesDatatableListQuery($searchs, $order, $limit, $offset);
        $qb
            ->leftJoin('place.user', 'user')
            ->where($qb->expr()->eq('type.code', ':typeShop'))
            ->andWhere('user.id IS NULL')
            ->setParameter('typeShop', Type::SHOP);

        return DatatableUtil::getQbData($this->_em, $qb, 'place.id', $searchs);
    }

    /**
     * @param $searchs
     * @param $order
     * @param $limit
     * @param $offset
     * @return array
     */
    public function getShopsDatatableList($searchs, $order, $limit, $offset)
    {
        $qb = $this->getPlacesDatatableListQuery($searchs, $order, $limit, $offset);
        $qb
            ->andWhere($qb->expr()->eq('type.code', ':typeShop'))
            ->setParameter('typeShop', Type::SHOP);

        return DatatableUtil::getQbData($this->_em, $qb, 'place.id', $searchs);
    }

    /**
     * @param $searchs
     * @param $order
     * @param $limit
     * @param $offset
     * @return array
     */
    public function getPartnersDatatableList($searchs, $order, $limit, $offset)
    {
        $qb = $this->getPlacesDatatableListQuery($searchs, $order, $limit, $offset);
        $qb
            ->andWhere($qb->expr()->eq('type.code', ':typeShop'))
            ->setParameter('typeShop', Type::PARTNER);

        return DatatableUtil::getQbData($this->_em, $qb, 'place.id', $searchs);
    }

    /**
     * @param $searchs
     * @param $order
     * @param $limit
     * @param $offset
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getPlacesDatatableListQuery($searchs, $order, $limit, $offset)
    {
        $qb = $this->_em->createQueryBuilder('place');
        $qb
            ->select(sprintf(
                'place.id as %s, place.name AS %s, place.email AS %s, placeAddress.address AS %s',
                PlaceService::DATATABLE_KEY_ID,
                PlaceService::DATATABLE_KEY_NAME,
                PlaceService::DATATABLE_KEY_EMAIL,
                PlaceService::DATATABLE_KEY_ADDRESS
            ))
            ->from('AppBundle:Place', 'place')
            ->innerJoin('place.type', 'type')
            ->innerJoin('place.address', 'placeAddress');

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
        return $qb;
    }

    /**
     * @param string $beerName
     * @return array
     */
    public function getHomeMapPlaces($beerName)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('place, address, type, beers')
            ->from('AppBundle:Place', 'place')
            ->innerJoin('place.address', 'address')
            ->innerJoin('place.type', 'type')
            ->leftJoin('place.beers', 'beers')
            ->leftJoin('beers.brewery', 'brewery')
            ->orderBy('brewery.name')
            ->addOrderBy('beers.name');

        if ($beerName) {
            $qb
                ->where('beers.name = :beerName')
                ->setParameter('beerName', $beerName);
        }

        $query = $qb->getQuery();
        $query->useQueryCache(true);
        return $query->getResult();
    }

    /**
     * @return array
     */
    public function getShopsReferenceIds()
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('place.mycollectionplacesReferenceId')
            ->from('AppBundle:Place', 'place')
            ->innerJoin('place.type', 'type')
            ->where('type.code = :shopType')
            ->setParameter('shopType', Type::SHOP);

        $query = $qb->getQuery();
        $query->useQueryCache(true);
        return $query->getResult();
    }


    public function getPlaceInformation($placeId, $locale)
    {
        $this->_em->createQueryBuilder()
            ->select('place, type, address, country, translations, beers, brewery, pictures')
            ->from('AppBundle:Place', 'place')
            ->innerJoin('place.type', 'type')
            ->innerJoin('place.address', 'address')
            ->innerJoin('address.country', 'country')
            ->innerJoin('country.translations', 'translations')
            ->leftJoin('place.beers', 'beers')
            ->leftJoin('beers.brewery', 'brewery')
            ->leftJoin('place.pictures', 'pictures');
        return [];
    }
}