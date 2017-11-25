<?php

namespace AppBundle\Form\Place;

use AppBundle\Service\ShopService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PictureType extends AbstractType
{
    /**
     * @var ShopService
     */
    private $shopService;

    /**
     * @var bool
     */
    private $inError;

    public function __construct(ShopService $shopService)
    {
        $this->shopService = $shopService;
        $this->inError = false;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'file',
                'hidden',
                ['required' => false]
            )
            ->addEventListener(FormEvents::SUBMIT, [$this, 'verifFile']);
    }

    /**
     * @param FormEvent $event
     */
    public function verifFile(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (empty($data->getFile())) {
            return;
        }
        if (!$this->shopService->verifFile($data->getFile())) {
            $form->get('file')->addError(new FormError(_('Invalid picture file.')));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => 'AppBundle\Entity\Place\Picture']);
    }

    public function getName()
    {
        return 'place_picture';
    }
}
