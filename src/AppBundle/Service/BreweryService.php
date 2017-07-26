<?php

namespace AppBundle\Service;

use AppBundle\Entity\Brewery;
use AppBundle\Entity\Language;
use AppBundle\Form\BreweryType;
use AppBundle\Util\DatatableUtil;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Translation\Translator;

class BreweryService extends AbstractService
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var BreweryType
     */
    private $breweryForm;

    const DATATABLE_KEY_ID           = 'id';
    const DATATABLE_KEY_NAME         = 'name';
    const DATATABLE_KEY_COUNTRY_ID   = 'country_id';
    const DATATABLE_KEY_COUNTRY_NAME = 'country_name';
    const DATATABLE_KEY_BEER_NUMBER  = 'beer_number';

    /**
     * @param EntityManager $manager
     * @param \Twig_Environment $twig
     * @param Translator $translator
     * @param FormFactoryInterface $formFactory
     * @param BreweryType $breweryForm
     */
    public function __construct(
        EntityManager $manager,
        \Twig_Environment $twig,
        Translator $translator,
        FormFactoryInterface $formFactory,
        BreweryType $breweryForm
    ) {
        parent::__construct($manager);
        $this->twig        = $twig;
        $this->translator  = $translator;
        $this->formFactory = $formFactory;
        $this->breweryForm = $breweryForm;
    }

    /**
     * @param $requestData
     * @param Language $language
     * @return array
     */
    public function getList($requestData, Language $language)
    {
        $listParams = $this->getListParams($requestData);

        $results = $this->manager->getRepository(Brewery::class)->getDatatableList(
            $listParams['searchs'],
            $listParams['order'],
            $listParams['limit'],
            $listParams['offset'],
            $language
        );

        $template = $this->twig->loadTemplate('admin/brewery/partial/datatable_items.html.twig');
        $data     = [];
        foreach ($results['data'] as $brewery) {
            $data[] = [
                $brewery[self::DATATABLE_KEY_NAME],
                $brewery[self::DATATABLE_KEY_COUNTRY_NAME],
                $brewery[self::DATATABLE_KEY_BEER_NUMBER],
                $template->renderBlock('btns', ['id' => $brewery[self::DATATABLE_KEY_ID]])
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
        $orderColumns  = [self::DATATABLE_KEY_NAME, self::DATATABLE_KEY_COUNTRY_NAME, self::DATATABLE_KEY_BEER_NUMBER];
        $searchColumns = [
            ['name' => self::DATATABLE_KEY_NAME, 'searchType' => DatatableUtil::SEARCH_LIKE],
            ['name' => self::DATATABLE_KEY_COUNTRY_ID, 'searchType' => DatatableUtil::SEARCH_EQUAL],
            ['name' => self::DATATABLE_KEY_BEER_NUMBER, 'searchType' => DatatableUtil::SEARCH_EQUAL]
        ];

        return [
            'searchs'  => DatatableUtil::getSearchs($requestData, $searchColumns),
            'order'    => DatatableUtil::getOrder($requestData, $orderColumns),
            'limit'    => DatatableUtil::getLimit($requestData),
            'offset'   => DatatableUtil::getOffset($requestData),
        ];
    }

    /**
     * @param Brewery $brewery
     */
    public function saveBrewery(Brewery $brewery)
    {
        $this->persistAndFlush($brewery);
    }

//
//    /**
//     * @param UploadedFile $file
//     * @return array|bool
//     */
//    public function parseFile(UploadedFile $file)
//    {
//        if ($file->getClientMimeType() !== 'text/csv') {
//            return false;
//        }
//        $csv = array_map('str_getcsv', file($file));
//        foreach ($csv as $index => $line) {
//            $csv[$index] = explode(';', $line[0])[0];
//        }
//        if ($csv[0] !== 'name') {
//            return false;
//        }
//        array_splice($csv, 0, 1);
//        return $csv;
//    }
//
//    /**
//     * @param $types
//     * @return array
//     */
//    public function importTypes($types)
//    {
//        $data = [];
//        foreach ($types as $index => $typeName) {
//            $data[$index] = ['name' => $typeName];
//            $type = new Type();
//            $form = $this->formFactory->create($this->beerTypeForm, $type, ['csrf_protection' => false]);
//            $form->submit(['translations' => $typeName]);
//            if (!$form->isValid()) {
//                $errors = $form->getErrors(true, true);
//                $data[$index]['error'] = $this->translator->trans($errors->offsetGet(0)->getMessage());
//            } else {
//                $this->saveType($type);
//            }
//        }
//        return $data;
//    }
}