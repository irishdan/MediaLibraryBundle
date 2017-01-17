<?php

namespace MediaLibraryBundle\Controller;

use MediaLibraryBundle\Entity\Image;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MediaLibraryBundle\Form\ImageType;

/**
 * Class MediaLibraryController
 * @package MediaLibraryBundle\Controller\Admin
 */
class MediaLibraryController extends Controller
{
    // @TODO: Use REST standard endpoints.
    /**
     * @return JsonResponse
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();
        $images = $em->getRepository('MediaLibraryBundle:Image')->findAll();

        $library = [];
        foreach ($images as $id => $image) {
            $this->get('responsive_image')->setPictureSet($image, 'media_library');
            $library[$image->getId()] = [
                'title' => $image->getTitle(),
                'image' => $image,
            ];
        }

        return new JsonResponse([
            'images' => $library,
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function newAction(Request $request)
    {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        // If submitted.
        if ($request->isMethod('POST')) {
            $formHandler = $this->get('media_library.image_form_handler');
            if ($formHandler->handle($form, $request)) {
                // Return the edit form.
                $image = $formHandler->getImage();
                $editForm = $this->createForm(ImageType::class, $image);

                // Get the form.
                $form = $this->renderView(
                    'image/form_ajax.html.twig',
                    array(
                        'image' => $image,
                        'edit_form' => $editForm->createView(),
                    )
                );

                // Return the actual image so it can be displayed in the widget.
                $this->get('responsive_image')->setPictureSet($image, 'image_field_widget');

                return new JsonResponse(
                    [
                        'status' => 'ok',
                        'action' => 'update_modal',
                        'modal_content' => $form,
                        'message' => [
                            'success' => 'Image created',
                        ],
                        'image_id' => $image->getId(),
                        'image' =>  $image,
                    ],
                    200
                );
            }
        }

        $form = $this->renderView(
            'image/form_new_ajax.html.twig',
            array(
                'image' => $image,
                'form' => $form->createView(),
            )
        );

        return new JsonResponse(
            ['form' => $form],
            200
        );
    }

    /**
     * @param Image $image
     * @return JsonResponse
     */
    public function showAction(Image $image)
    {
        $em = $this->getDoctrine()->getManager();
        $image = $em->getRepository('MediaLibraryBundle:Image')->findOneBy(['id' => $image->getId()]);

        // @TODO: Set this style automatically if it not already set.
        // $image->setAttributes(['class' => 'img-circle' ]);
        $this->get('responsive_image')->setPictureSet($image, 'image_field_widget');

        return new JsonResponse([
            'image' => $image,
        ], 200);
    }

    /**
     * @param Request $request
     * @param Image $image
     * @return JsonResponse
     */
    public function editAction(Request $request, Image $image)
    {
        $editForm = $this->createForm(ImageType::class, $image);
        if ($request->isMethod('POST')) {
            $formHandler = $this->get('media_library.image_form_handler');
            if ($formHandler->handle($editForm, $request)) {
                // Submitting the form.
                return new JsonResponse(
                    [
                        'status' => 'ok',
                        'action' => 'close_modal',
                        'message' => [
                            'success' => 'Image updated',
                        ],
                    ],
                    200
                );
            }
        }
        else {
            // Getting the form.
            $form = $this->renderView(
                'image/form_ajax.html.twig',
                array(
                    'image' => $image,
                    'edit_form' => $editForm->createView(),
                )
            );

            return new JsonResponse(
                ['form' => $form],
                200
            );
        }
    }
}