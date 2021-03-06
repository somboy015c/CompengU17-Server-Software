<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div id="video_upload_result">
    <?php $this->load->view('product/_video_upload_response'); ?>
</div>

<!-- File item template -->
<script type="text/html" id="files-template-video">
    <li class="media">
        <div class="media-body">
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </li>
</script>

<script>
    $('#drag-and-drop-zone-video').dmUploader({
        <?php if (!is_storage_full($user)): ?>
        url: '<?php echo base_url(); ?>file_controller/upload_video',
        maxFileSize: <?php echo $this->general_settings->max_file_size_video; ?>,
        queue: true,
        extFilter: ["mp4", "webm"],
        multiple: false,
        extraData: {
            "product_id":<?php echo $product->id; ?>
        },
        onDragEnter: function () {
            this.addClass('active');
        },
        onDragLeave: function () {
            this.removeClass('active');
        },
        onInit: function () {
        },
        onComplete: function (id) {
        },
        onNewFile: function (id, file) {
            ui_multi_add_file(id, file, "video");
        },
        onBeforeUpload: function (id) {
            ui_multi_update_file_progress(id, 0, '', true);
            ui_multi_update_file_status(id, 'uploading', 'Uploading...');
        },
        onUploadProgress: function (id, percent) {
            ui_multi_update_file_progress(id, percent);
        },
        onUploadSuccess: function (id, product_id) {
            load_video_preview(product_id);
            var data = {
                "product_id": product_id,
            };
            data[csfr_token_name] = $.cookie(csfr_cookie_name);
            $.ajax({
                url: base_url + "file_controller/delete_audio",
                type: "post",
                data: data,
                success: function (response) {
                    document.getElementById("audio_upload_result").innerHTML = response;
                }
            });
        },
        onUploadError: function (id, xhr, status, message) {
        },
        onFallbackMode: function () {
        },
        onFileSizeError: function (file) {
            $(".error-message-img-upload").show();
            $(".error-message-img-upload p").html("<?php echo trans('file_too_large') . ' ' . formatSizeUnits($this->general_settings->max_file_size_video); ?>");
            setTimeout(function () {
                $(".error-message-img-upload").fadeOut("slow");
            }, 4000)
        },
        onFileTypeError: function (file) {
        },
        onFileExtError: function (file) {
        },

        <?php else: ?>
        onDragEnter: function () {
            $(".error-message-img-upload").show();
            $(".error-message-img-upload p").html("<?php echo ('You are out of storage space, free up some space and try again'); ?>");
            setTimeout(function () {
                $(".error-message-img-upload").fadeOut("slow");
            }, 4000)
        },
        onNewFile: function () {
            $(".error-message-img-upload").show();
            $(".error-message-img-upload p").html("<?php echo ('You are out of storage space, free up some space and try again'); ?>");
            setTimeout(function () {
            $(".error-message-img-upload").fadeOut("slow");
            }, 4000)
        }
        <?php endif; ?>
    });
    $(document).ajaxStop(function () {
        $('#drag-and-drop-zone-video').dmUploader({
            <?php if (!is_storage_full($user)): ?>
            url: '<?php echo base_url(); ?>file_controller/upload_video',
            maxFileSize: <?php echo $this->general_settings->max_file_size_video; ?>,
            queue: true,
            extFilter: ["mp4", "webm"],
            multiple: false,
            extraData: {
                "product_id":<?php echo $product->id; ?>
            },
            onDragEnter: function () {
                this.addClass('active');
            },
            onDragLeave: function () {
                this.removeClass('active');
            },
            onInit: function () {
            },
            onComplete: function (id) {
            },
            onNewFile: function (id, file) {
                ui_multi_add_file(id, file, "video");
            },
            onBeforeUpload: function (id) {
                ui_multi_update_file_progress(id, 0, '', true);
                ui_multi_update_file_status(id, 'uploading', 'Uploading...');
            },
            onUploadProgress: function (id, percent) {
                ui_multi_update_file_progress(id, percent);
            },
            onUploadSuccess: function (id, product_id) {
                load_video_preview(product_id);
                var data = {
                    "product_id": product_id,
                };
                data[csfr_token_name] = $.cookie(csfr_cookie_name);
                $.ajax({
                    url: base_url + "file_controller/delete_audio",
                    type: "post",
                    data: data,
                    success: function (response) {
                        document.getElementById("audio_upload_result").innerHTML = response;
                    }
                });
            },
            onUploadError: function (id, xhr, status, message) {
            },
            onFallbackMode: function () {
            },
            onFileSizeError: function (file) {
                $(".error-message-img-upload").show();
                $(".error-message-img-upload p").html("<?php echo trans('file_too_large') . ' ' . formatSizeUnits($this->general_settings->max_file_size_video); ?>");
                setTimeout(function () {
                    $(".error-message-img-upload").fadeOut("slow");
                }, 4000)
            },
            onFileTypeError: function (file) {
            },
            onFileExtError: function (file) {
            },

            <?php else: ?>
            onDragEnter: function () {
                $(".error-message-img-upload").show();
                $(".error-message-img-upload p").html("<?php echo ('You are out of storage space, free up some space and try again'); ?>");
                setTimeout(function () {
                    $(".error-message-img-upload").fadeOut("slow");
                }, 4000)
            },
            onNewFile: function () {
                $(".error-message-img-upload").show();
                $(".error-message-img-upload p").html("<?php echo ('You are out of storage space, free up some space and try again'); ?>");
                setTimeout(function () {
                $(".error-message-img-upload").fadeOut("slow");
                }, 4000)
            }
            <?php endif; ?>
        });
    });

    function load_video_preview(product_id) {
        var data = {
            "product_id": product_id
        };
        data[csfr_token_name] = $.cookie(csfr_cookie_name);
        $.ajax({
            type: "POST",
            url: base_url + "file_controller/load_video_preview",
            data: data,
            success: function (response) {
                setTimeout(function () {
                    document.getElementById("video_upload_result").innerHTML = response;
                    const player = new Plyr('#player');
                }, 15000);
            }
        });
    }
</script>

