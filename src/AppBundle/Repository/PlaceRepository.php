<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Language;
use AppBundle\Entity\Place;
use AppBundle\Entity\Place\Type;
use AppBundle\Service\PlaceService;
use AppBundle\Util\DatatableUtil;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

class PlaceRepository extends EntityRepository
{
    /**
     * @param $searchs
     * @param $order
     * @param $limit
     * @param $offset
     *
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
     *
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
     *
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
     *
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

        if (null !== $order) {
            $qb->orderBy($order['col'], $order['dir']);
        }

        if (null !== $offset && null !== $limit) {
            $qb->setFirstResult($offset);
        }
        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }
        if (null !== $searchs) {
            foreach ($searchs as $search) {
                $expr = $search['expr'];
                $paramKey = $search['param']['key'];
                $paramValue = $search['param']['value'];

                $qb
                    ->orHaving($expr)
                    ->setParameter($paramKey, $paramValue);
            }
        }

        return $qb;
    }

    /**
     * @param $beerId
     *
     * @return array
     */
    public function getHomeMapPlaces($beerId)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('place, user, address, type, beers')
            ->from('AppBundle:Place', 'place')
            ->innerJoin('place.address', 'address')
            ->innerJoin('place.type', 'type')
            ->leftJoin('place.user', 'user')
            ->leftJoin('place.beers', 'beers')
            ->leftJoin('beers.brewery', 'brewery')
            ->orderBy('beers.name');

        if ($beerId) {
            $qb
                ->where('beers.id = :beerId')
                ->setParameter('beerId', $beerId);
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

    /**
     * @param $placeId
     * @param \AppBundle\Entity\Language $language
     *
     * @return \AppBundle\Entity\Place|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getShopInformation($placeId, Language $language = null)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('place, user, type, address, country, country_translations, beers, brewery, pictures, schedules')
            ->from('AppBundle:Place', 'place')
            ->innerJoin('place.type', 'type')
            ->leftJoin('place.user', 'user')
            ->innerJoin('place.address', 'address')
            ->innerJoin('address.country', 'country')
            ->leftJoin('country.translations', 'country_translations', Expr\Join::WITH, 'country_translations.language = :language')
            ->leftJoin('place.beers', 'beers')
            ->leftJoin('beers.type', 'beer_type')
            ->leftJoin('beer_type.translations', 'beer_type_translations', Expr\Join::WITH, 'beer_type_translations.language = :language')
            ->leftJoin('beers.brewery', 'brewery')
            ->leftJoin('place.pictures', 'pictures')
            ->leftJoin('place.schedules', 'schedules')
            ->where('place.id = :id')
            ->orderBy('beers.name')
            ->setParameters([
                'id' => $placeId,
                'language' => $language,
            ]);

        $query = $qb->getQuery();
        $query->useQueryCache(true);

        return $query->getOneOrNullResult();
    }

    /**
     * @return Place|null
     */
    public function getNewestShop()
    {
        return $this->getNewestPlace(Type::SHOP);
    }

    /**
     * @return Place|null
     */
    public function getNewestPartner()
    {
        return $this->getNewestPlace(Type::PARTNER);
    }

    /**
     * @return mixed
     */
    public function getShopsCount()
    {
        return $this->getPlacesCount(Type::SHOP);
    }

    /**
     * @return mixed
     */
    public function getPartnersCount()
    {
        return $this->getPlacesCount(Type::PARTNER);
    }

    /**
     * @param $typeCode
     *
     * @return Place|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getNewestPlace($typeCode)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('place, user')
            ->from('AppBundle:Place', 'place')
            ->innerJoin('place.type', 'type')
            ->leftJoin('place.user', 'user')
            ->where('type.code = :typeCode')
            ->orderBy('place.id', 'DESC')
            ->setMaxResults(1)
            ->setParameter('typeCode', $typeCode);
        $query = $qb->getQuery();
        $query->useQueryCache(true);

        return $query->getOneOrNullResult();
    }

    /**
     * @param $typeCode
     *
     * @return mixed
     */
    private function getPlacesCount($typeCode)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('count(place)')
            ->from('AppBundle:Place', 'place')
            ->innerJoin('place.type', 'type')
            ->where('type.code = :typeCode')
            ->setParameter('typeCode', $typeCode);
        $query = $qb->getQuery();
        $query->useQueryCache(true);

        return $query->getSingleScalarResult();
    }
}
