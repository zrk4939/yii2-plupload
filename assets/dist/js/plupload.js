var countFiles = 0;
var uploadedFiles = 0;
var errorContainer = document.getElementsByClassName('error-summary');
var $submitBtn = document.getElementsByTagName('button');
var $browseBtn = document.getElementsByClassName('browse');
var $progressBar = document.getElementsByClassName('plupload-progress');

function plupFilesAdded(uploader, files) {
    $(errorContainer).hide();
    $(errorContainer).find('ul').children().remove();

    // $($browseBtn).button("loading");
    // $($submitBtn).button("loading");

    countFiles = files.length;

    $($progressBar).removeClass('hidden');
    uploader.start();
}

function plupFileUploaded(uploader, file, response) {
    var result = JSON.parse(response.response);

    if (result.status == 'ok') {
        var $newFileInput = $("<input type='hidden'/>")
            .attr('name', result.inputName)
            .val(result.fileName);

        if (result.isImage) {
            showImagePreview(file, ".to-upload-" + result.attr, $newFileInput, result);
        }
        else {
            showFilePreview(file, ".to-upload-" + result.attr, $newFileInput, result);
        }
    } else {
        $(errorContainer).find('ul').append($("<li>" + result.message + "</li>"));
        $(errorContainer).show();
    }

    uploadedFiles++;

    if (uploadedFiles == countFiles) {
        // $($browseBtn).button("reset");
        // $($submitBtn).button("reset");
        $($progressBar).addClass('hidden');
        uploadedFiles = countFiles = 0;
    }
}

function showFilePreview(file, previewClass, $input, result) {
    var $previewWrap = $(previewClass),
        $preview = $('<span />')
            .addClass('file')
            .data('filename', file.name)
            .attr('title', file.name)
            .append(file.name)
            .append($input)
            .append($('<span class="glyphicon glyphicon-remove cancel"></span>'))
            .appendTo($previewWrap);
}

function showImagePreview(file, previewClass, $input, result) {
    var $previewWrap = $(previewClass),
    $preview = $('<a />')
        .addClass('file image-preview')
        .css({'background-image': 'url(' + result.preview + ')'})
        .data('filename', file.name)
        .attr('title', file.name)
        .append($input)
        .append($('<span class="glyphicon glyphicon-remove cancel"></span>'))
        .appendTo($previewWrap);
}

function plupError(uploader, error) {
    $(errorContainer).find('ul').append($("<li>" + error.message + "</li>"));
    $(errorContainer).show();
    $($progressBar).addClass('hidden');
    // $($browseBtn).button("reset");
    // $($submitBtn).button("reset");
}

$(function () {
    $(document).on('click', '.preview .cancel', function (e) {
        e.preventDefault();

        var $imgPreview = $(this).parent('.file'),
            filename = $imgPreview.attr('data-filename'),
            deleteWrapClass = $(this).data('wrapper-class'),
            $deleteWrap = $(document.getElementsByClassName(deleteWrapClass)),
            delInputName = $deleteWrap.data('delete-name'),
            $deleteInput = $('<input type="hidden" name="' + delInputName + '"/>');

        $deleteWrap.append($deleteInput.val(filename).clone());
        $imgPreview.remove();

        return false;
    });

    $(document).on('click', '.show-attributes-modal', function (event) {
        event.preventDefault();

        var $this = $(this),
            $modal = $('#update-attributes'),
            $modalBody = $modal.find('.modal-body');

        $modalBody.empty();

        if ($modal.data('bs.modal').isShown) {
            $modalBody.load($this.attr('href'));

            // $modal.find('.modal-header').html('<h4>' + $this.data('title') + '</h4>');
        } else {
            $modal.modal('show');
            $modalBody.load($this.attr('value'));

            // $modal.find('.modal-header').html('<h4>' + $this.data('title') + '</h4>');
        }

        return false;
    });
});