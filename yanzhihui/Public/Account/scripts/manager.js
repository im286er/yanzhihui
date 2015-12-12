$(function() {
    /* controls Swfupload */
    if($(".dEdit .upLoadPicOther").length) {
        /* initSwfuploadOther */
        function initSwfuploadOther(i) {
            new SWFUpload({
                upload_url: "upload",
                flash_url : ruis.STATIC_URL + "/controls/Swfupload/swfupload.swf",

                file_size_limit : "1024", //1M
                file_types : "*.jpg;*.png;*.gif;*.bmp",
                file_types_description : "Web Image Files",

                file_dialog_start_handler : fileDialogStart,
                file_queued_handler : fileQueued,
                file_queue_error_handler : fileQueueError,
                file_dialog_complete_handler : fileDialogComplete,
                upload_start_handler : uploadStart,
                upload_progress_handler : uploadProgress,
                upload_error_handler : uploadError,
                upload_success_handler : uploadSuccessOther,
                upload_complete_handler : uploadComplete,

                button_image_url : ruis.STATIC_URL + "/controls/Swfupload/swfUploadBtn.png",
                button_placeholder_id : "uploadBtnOther" + i,
                button_width: 100,
                button_height: 30,

                custom_settings : {
                    progressTarget : "uploadProgressOther" + i,
                    cancelButtonId : "cancelBtnOther" + i
                }
            })
        }
        $(".dEdit .upLoadPicOther").each(function(i) {
            $(this).find(".progressWrapperBg").attr("id", "uploadProgressOther" + i);
            $(this).find(".uploadBtnBg .aCancel").attr("id", "cancelBtnOther" + i);
            $(this).find(".uploadBtnBg .aUpload").append('<a id="uploadBtnOther'+ i +'"></a>');
            initSwfuploadOther(i);
        });
    }
})