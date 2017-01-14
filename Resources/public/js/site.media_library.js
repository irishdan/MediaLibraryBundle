(function($) {
    Site.media_library = {
        action: 'init',

        actionUrls: {
            'create_image': '/admin/media-library/new',
            'edit_image': '/admin/media-library/edit/{id}',
            'get_chosen_image' : '/admin/media-library/show/{id}',
            'remove_image': '/admin/media-library',
            'select_image': '/admin/media-library'
        },

        data: '',

        formAction: '',

        imageId: '',

        modal: '',

        process: '',

        response: '',

        queryNumber: 0,

        ready: function() {
            var $fields = $('.image-widget');

            // Move the modal window to the bottom of the page.
            $('.image-field-widget-modal').appendTo(document.body);

            $fields.each(function() {
                // Set the image initial id
                Site.media_library.imageId = $('#user_avatar .image-field-image',this).val();
                console.log(Site.media_library.imageId);
                $('.action-control', this).click(function(e) {
                    e.preventDefault();

                    // Get the action
                    Site.media_library.action = $(this).attr('data-action');

                    // Start doing the things.
                    Site.media_library.setDeffered();

                    // Do the action.
                    Site.media_library.doAction();

                    return false;
                });

                Site.media_library.updateActionButtons();
            });

            this.modal = $('.media-library-modal-lg');
            this.modal.on('click', 'a', function() {
                // Get the action
                Site.media_library.action = 'get_chosen_image';
                Site.media_library.imageId = $(this).attr('data-id');
                Site.media_library.setDeffered();
                Site.media_library.doAction();

                return false;
            });

            this.modal.on('submit', 'form', function() {
                console.log('modal form submit event');
                Site.media_library.action = 'modal_form_submit';
                Site.media_library.setDeffered();

                Site.media_library.data = new FormData(this);
                Site.media_library.formAction = $(this).attr('action');

                Site.media_library.doAction();

                return false;
            });
        },

        submitModalForm: function() {
            $.ajax({
                type: 'POST',
                url: Site.media_library.formAction,
                data: Site.media_library.data,
                contentType: false,
                cache: false,
                async: false,
                processData: false,
                success: function(response) {
                    Site.media_library.response = response;
                    Site.media_library.process.resolve();
                },
            });
        },

        setDeffered: function() {
            Site.media_library.process = $.Deferred();
            Site.media_library.process.done(function() {
                Site.media_library.doneAction();
            });
        },

        doAction: function() {
            var action = Site.media_library.action;

            switch(action) {
                case 'create_image':
                    Site.media_library.modalClearOpen();
                    Site.media_library.getData(null, action,'insertForm');
                    break;

                case 'edit_image':
                    Site.media_library.modalClearOpen();
                    Site.media_library.getData(Site.media_library.imageId, action,'insertForm');
                    break;

                case 'get_chosen_image':
                    Site.media_library.getData(Site.media_library.imageId, action,'insertSelectedImage');
                    break;

                case 'remove_image':
                    Site.media_library.updateWidget('', '');
                    Site.media_library.process.resolve();
                    break;

                case 'select_image':
                    Site.media_library.modalClearOpen();
                    Site.media_library.getData('', action,'insertImages');
                    break;

                case 'modal_form_submit':
                    // Submit form and deal with response.
                    Site.media_library.submitModalForm();
                    break;

                default:
                    break;
            }
        },

        doneAction: function() {
            var action = Site.media_library.action;
            console.log(action);
            switch (action) {
                case 'get_chosen_image':
                    Site.media_library.updateActionButtons();
                    break;

                case 'remove_image':
                    Site.media_library.updateActionButtons();
                    break;

                case 'select_image':
                    // Nuffin to see here.
                    break;

                case 'create_image':

                    Site.media_library.setModalFormAction();
                    break;

                case 'edit_image':
                    // Initialise the edit widget is it exists.
                    $('.crop-focus-image img').imageCrop();
                    Site.media_library.setModalFormAction();
                    break;

                case 'modal_form_submit':
                    // Deal with submitted form response.
                    var response = Site.media_library.response;
                    console.log(response);
                    if (response.status == 'ok') {
                        if (response.action == 'close_modal') {
                            Site.media_library.modal.modal('hide');
                            // Reload widget image.
                            Site.media_library.reloadImage();
                        }
                        if (response.action == 'update_modal') {
                            // Update the image id.
                            Site.media_library.imageId = response.image_id;

                            // Update the image widget.
                            Site.media_library.updateWidget(response.image_id, response.image.picture);
                            Site.media_library.updateActionButtons();

                            // Update the action
                            Site.media_library.action = 'edit_image';

                            // Update the modal.
                            Site.media_library.modalAddContent(response.modal_content);

                            // Initialize the widget.
                            $('.crop-focus-image img').imageCrop();

                            // Update the form action
                            Site.media_library.setModalFormAction();
                        }
                    }

                    break;
            }

        },

        reloadImage: function() {
            var $picture = $('.selected-image picture');

            Site.media_library.queryNumber++;
            $('img, source', $picture).each(function() {
                var srcset = $(this).attr('srcset');
                srcset = srcset + '?dan=' + Site.media_library.queryNumber;
                $(this).attr('srcset', srcset);
            });
        },

        modalClearOpen: function() {
            var $modal = Site.media_library.modal,
                $body = $('.modal-body', $modal);

            // Clear the content,
            $body.html('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');

            // Open the modal.
            $modal.addClass('ready');
            $modal.modal();
        },

        modalAddContent: function(content) {
            var $modal = Site.media_library.modal,
                $body = $('.modal-body', $modal);

            // Insert content.
            $body.html(content);

            // Resole ve the deferred.
            Site.media_library.process.resolve();
        },

        insertForm: function(data) {
            Site.media_library.modalAddContent(data['form']);
        },

        insertImages: function(data) {
            var content = '';
            $.each(data.images, function(key, value) {
                var item = '<a href="" title="' + value.title + '" data-id="' + key + '" class="col-md-2 library-item no-padding">';
                item += value.image.picture;
                item += '</a>'
                content += item;
            });

            Site.media_library.modalAddContent(content);
        },

        insertSelectedImage: function(data) {
            Site.media_library.modal.modal('hide');
            Site.media_library.updateWidget(Site.media_library.imageId, data.image.picture);
            Site.media_library.process.resolve('edit');
        },

        getData: function(id, action, callback) {
            var url =  Site.media_library.actionUrls[action];

            // If id exists
            if (id > 0) {
                url = url.replace('{id}', Site.media_library.imageId);
            }

            $.ajax({
                url: url,
                data: [],
                success: function(jsonData) {
                    // Use the callback function name
                    Site.media_library[callback](jsonData);
                    Site.media_library.process.resolve();
                },
                failure: function() {
                    // Faily mac failface.
                },
                dataType: 'JSON',
            });
        },

        setModalFormAction: function() {
            var action = Site.media_library.action,
                url = Site.media_library.actionUrls[action],
                id = Site.media_library.imageId,
                actionUrl = url.replace('{id}', id);

            $('form', Site.media_library.modal).attr('action', actionUrl);
        },

        updateActionButtons: function() {
            var $actions = $('.action-controls');
            var action = Site.media_library.action;
            console.log(action);
            switch(action) {
                case 'create_image':
                    break;

                case 'get_chosen_image':
                    $actions.removeClass('action-create').addClass('action-edit');
                    break;

                case 'remove_image':
                    $actions.removeClass('action-edit').addClass('action-create');
                    break;

                case 'init':
                    var id = Site.media_library.imageId;
                    if (id == '' || typeof id == 'undefined') {
                        console.log('id not set');
                        $actions.removeClass('action-edit').addClass('action-create');
                    }
                    else {
                        console.log('ID set to ' + id);
                        $actions.removeClass('action-create').addClass('action-edit');
                    }
                    break;
            }
        },

        updateWidget: function(imageId, imageHtml) {
            console.log(imageId);
            console.log(imageHtml);

            $('#user_avatar .image-field-image').val(imageId);
            $('#user_avatar .selected-image').html(imageHtml);
        }
    };
})(jQuery);