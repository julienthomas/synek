<?php

namespace AppBundle\Form;

use AppBundle\Entity\Beer;
use AppBundle\Entity\Language;
use AppBundle\Form\Place\AddressType;
use AppBundle\Form\Place\PictureType;
use AppBundle\Form\Place\ScheduleType;
use AppBundle\Service\ShopService;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlaceType extends AbstractType
{
    /**
     * @var Language
     */
    private $language;

    /**
     * @var ShopService
     */
    private $shopService;

    /**
     * @var bool
     */
    private $isShop;

    /**
     * @var bool
     */
    private $addUser;

    /**
     * @param Language $language
     * @param ShopService $shopService
     * @param bool $isShop
     * @param bool $addUser
     */
    public function __construct(Language $language, ShopService $shopService = null, $isShop = false, $addUser = false)
    {
        $this->language    = $language;
        $this->shopService = $shopService;
        $this->isShop      = $isShop;
        $this->addUser     = $addUser;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'text',
                ['label' => 'Name']
            )
            ->add(
                'email',
                'email',
                [
                    'label'    => 'Email',
                    'required' => false
                ]
            )
            ->add(
                'phone',
                'text',
                [
                    'label'    => 'Phone number',
                    'required' => false
                ]
            )
            ->add(
                'address',
                new AddressType($this->language)
            )
            ->add(
                'website',
                'url',
                [
                    'label'    => 'Website',
                    'required' => false
                ]
            )
            ->add(
                'facebook',
                'url',
                [
                    'label'    => 'Facebook',
                    'required' => false
                ]
            );

        if ($this->isShop) {
            $builder
                ->add(
                    'description',
                    'textarea',
                    [
                        'label'    => 'Description',
                        'required' => false
                    ]
                )
                ->add(
                    'beers',
                    'entity',
                    [
                        'class'         => Beer::class,
                        'required'      => false,
                        'attr'          => ['title' => '- Choose one or more -'],
                        'multiple'      => true,
                        'choice_label'  => 'name',
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('beer')
                                ->addSelect('brewery')
                                ->innerJoin('beer.brewery', 'brewery')
                                ->addOrderBy('brewery.name')
                                ->orderBy('beer.name');
                        },
                        'group_by' => function($val) {
                            return $val->getBrewery()->getName();
                        },
                        'choice_attr' => function($val) {
                            return ['data-tokens' => $val->getBrewery()->getName()];
                        }
                    ]
                )
                ->add(
                    'schedules',
                    'collection',
                    [
                        'type'         => new ScheduleType(),
                        'required'     => false,
                        'allow_add'    => true,
                        'allow_delete' => true,
                        'by_reference' => true
                    ]
                );
            if ($this->shopService) {
                $builder->add(
                    'pictures',
                    'collection',
                    [
                        'type'         => new PictureType($this->shopService),
                        'allow_add'    => true,
                        'allow_delete' => true
                    ]
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'AppBundle\Entity\Place',
            'cascade_validation' => true
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'place';
    }
}