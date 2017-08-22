<?php

namespace AppBundle\Form\Place;

use AppBundle\Entity\Language;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    /**
     * @var Language
     */
    private $language;

    /**
     * @param Language $language
     */
    public function __construct(Language $language)
    {
        $this->language = $language;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'address',
                'text',
                ['label' => _('Address')]
            )
            ->add(
                'addressComplement',
                'text',
                [
                    'label'    => _('Address Complement'),
                    'required' => false
                ]
            )
            ->add(
                'zipCode',
                'text',
                ['label' => _('Zip code')]
            )
            ->add(
                'city',
                'text',
                ['label' => _('City')]
            )
            ->add(
                'country',
                'entity',
                [
                    'label'         => _('Country'),
                    'empty_value'   => '- ' . _('Choose one') . ' -',
                    'class'         => 'AppBundle\Entity\Country',
                    'choice_label'  => 'translations.first.name',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('country')
                            ->addSelect('translations')
                            ->leftJoin('country.translations', 'translations')
                            ->leftJoin('translations.language', 'language')
                            ->where('language.locale = :locale')
                            ->setParameter('locale', $this->language->getLocale())
                            ->orderBy('translations.name', 'ASC');
                    },
                ]
            )
            ->add(
                'latitude', 'text', ['required' => false]
            )
            ->add(
                'longitude', 'text', ['required' => false]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => 'AppBundle\Entity\Place\Address']);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'place_address';
    }
}