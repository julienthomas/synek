<?php

namespace AppBundle\Repository\Beer;

use AppBundle\Entity\Beer\Type;
use AppBundle\Entity\Language;
use AppBundle\Service\BeerTypeService;
use AppBundle\Util\DatatableUtil;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

class TypeRepository extends EntityRepository
{
    /**
     * @param $searchs
     * @param $order
     * @param $limit
     * @param $offset
     * @return array
     */
    public function getDatatableList($searchs, $order, $limit, $offset)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select(sprintf(
                'type.id as %s, translation.name as %s, count(beers) as %s',
                BeerTypeService::DATATABLE_KEY_ID,
                BeerTypeService::DATATABLE_KEY_NAME,
                BeerTypeService::DATATABLE_BEER_NUMBER
            ))
            ->from('AppBundle:Beer\Type', 'type')
            ->leftJoin('type.translations', 'translation')
            ->leftJoin('translation.language', 'translation_language')
            ->leftJoin('AppBundle:Beer', 'beers', Join::WITH, 'beers.type = type')
            ->where('translation_language.locale = :locale')
            ->groupBy('type.id')
            ->setParameter('locale', 'fr_FR');

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

        return DatatableUtil::getQbData($this->_em, $qb, 'type.id', $searchs);
    }

    /**
     * @param Type $type
     * @return array
     */
    public function getTypeWithTranslations(Type $type)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select('type, translation')
            ->from('AppBundle:Beer\Type', 'type')
            ->innerJoin('type.translations', 'translation')
            ->innerJoin('translation.language', 'language')
            ->where('type = :type')
            ->setParameter('type', $type);

        $query = $qb->getQuery();
        $query->useQueryCache(true);
        return $query->getResult();
    }

    /**
     * @param $language
     * @return array
     */
    public function getTypesWithTranslation(Language $language)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select('type, translations')
            ->from('AppBundle:Beer\Type', 'type')
            ->innerJoin('type.translations', 'translations')
            ->where('translations.language = :language')
            ->orderBy('translations.name')
            ->setParameter('language', $language);

        $query = $qb->getQuery();
        $query->useQueryCache(true);
        return $query->getResult();
    }
}