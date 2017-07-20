<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class CountryRepository extends EntityRepository
{
    /**
     * @param $ids
     * @return array
     */
    public function findByIds($ids)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('country')
            ->from('AppBundle:Country', 'country')
            ->where('country.id IN :ids')
            ->setParameter('ids', $ids);

        $query = $qb->getQuery();
        $query->useQueryCache(true);

        return $query->getResult();
    }
}