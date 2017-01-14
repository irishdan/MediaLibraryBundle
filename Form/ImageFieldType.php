<?php

namespace MediaLibraryBundle\Form;

use MediaLibraryBundle\Form\DataTransformer\ImageToIdTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;


/**
 * Class ImageFieldType
 * @package AppBundle\Form
 */
class ImageFieldType extends AbstractType
{
    /**
     * @var
     */
    private $image;

    /**
     * @var
     */
    private $imageManager;

    /**
     * @var
     */
    private $objectManager;

    /**
     * ImageFieldType constructor.
     * @param $imageManager
     * @param $objectManager
     */
    public function __construct($imageManager, $objectManager)
    {
        $this->imageManager = $imageManager;
        $this->objectManager = $objectManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('weight', HiddenType::class, array(
                'label' => FALSE,
                'data' => 0,
            ))
            ->add('image', HiddenType::class)
        ;

        // Add a transformer to convert id to image object.
        $builder->get('image')->addModelTransformer(new ImageToIdTransformer($this->objectManager));
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $data = $form->getData();
        if (!empty($data)) {
            $image = $data->getImage();
        }

        if (!empty($image)) {
            $image = $this->imageManager->setPictureSet($image, 'article_edit');
            $options['image'] = $image;

            $view->vars = array_replace($view->vars, $options);
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MediaLibraryBundle\Entity\ImageField'
        ));
    }
}
