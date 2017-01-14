<?php

namespace MediaLibraryBundle\Form\Handler;

use MediaLibraryBundle\Utils\Uploader;
use ResponsiveImageBundle\Event\ImageEvent;
use ResponsiveImageBundle\Event\ImageEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ImageFormHandler
 * @package AppBundle\Form\Handler
 */
class ImageFormHandler
{
    /**
     * @var
     */
    private $entityManager;

    /**
     * @var
     */
    private $imageFileSystem;

    /**
     * @var
     */
    private $image;

    /**
     * @var
     */
    private $uploader;

    /**
     * @var
     */
    private $dispatcher;

    /**
     * ImageFormHandler constructor.
     * @param $imageFileSystem
     * @param $entityManager
     * @param $uploader
     */
    public function __construct($imageFileSystem, $entityManager, Uploader $uploader, EventDispatcherInterface $dispatcher)
    {
        $this->imageFileSystem = $imageFileSystem;
        $this->entityManager = $entityManager;
        $this->uploader = $uploader;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @return bool
     */
    public function handle(FormInterface $form, Request $request)
    {
        if (!$request->isMethod('POST')) {
            return false;
        }
        $form->handleRequest($request);

        if (!$form->isValid()) {
            return false;
        }
        $validImage = $form->getData();

        $id = $validImage->getId();
        // If the id is not set then it's a create form.
        if (empty($id)) {
            // Upload the file.
            $this->uploader->setFileSystem($this->imageFileSystem);
            $this->uploader->upload($validImage);
            $this->image = $validImage;

            $this->entityManager->persist($validImage);
            $this->entityManager->flush();
        }
        // If the image has an ID then its an existing image.
        else {
            // @TODO: We need a way to swap the image files not just update the.
            $this->entityManager->persist($validImage);
            $this->entityManager->flush();
        }

        // Dispatch style generate event to the listeners.
        $event = new ImageEvent($validImage);
        $this->dispatcher->dispatch(
            ImageEvents::IMAGE_GENERATE_STYLED,
            $event
        );

        return TRUE;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }
}