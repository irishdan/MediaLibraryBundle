<?php

namespace MediaLibraryBundle\Form\DataTransformer;

use MediaLibraryBundle\Entity\Image;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ImageToIdTransformer implements DataTransformerInterface
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Transforms an object (image) to a string (id).
     *
     * @param  Image|null $image
     * @return string
     */
    public function transform($image)
    {
        if (null === $image) {
            return '';
        }

        return $image->getId();
    }

    /**
     * Transforms a string (id) to an object (image).
     *
     * @param  string $imageId
     * @return Image|null
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($imageId)
    {
        // No image id.
        if (!$imageId) {
            return;
        }

        $image = $this->manager
            ->getRepository('MediaLibraryBundle:Image')
            ->findOneBy(array('id' => array($imageId)));
        ;

        if (null === $image) {
            // Causes a validation error.
            throw new TransformationFailedException(sprintf(
                'An image with id "%s" does not exist!',
                $imageId
            ));
        }

        return $image;
    }
}