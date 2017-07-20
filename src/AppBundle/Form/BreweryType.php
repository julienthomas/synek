<?php

namespace AppBundle\Form;

use AppBundle\Entity\Brewery;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Language;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BreweryType extends AbstractType
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
                'name',
                'text',
                ['label' => 'Name']
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
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Brewery::class);
    }

    public function getName()
    {
        return 'brewery';
    }
}