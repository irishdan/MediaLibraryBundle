<?php

namespace MediaLibraryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use ResponsiveImageBundle\Form\Type\CropFocusType;


class ImageType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('alt')
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $image = $event->getData();
            $form = $event->getForm();

            // Conditionally add form elements.
            if (!empty($image) && !empty($image->getId())) {
                $form->add('crop_coordinates', CropFocusType::class, array(
                    'data' => $image
                ));
            }
            else {
                $form->add('file', FileType::class, array('label' => 'Upload an image'));
            }
        });
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MediaLibraryBundle\Entity\Image'
        ));
    }
}
