/* common var */
var ruis = {
    TIME:1500,
    STATIC_URL:"/Public/Static",
    DEFAULT_UPLOAD_IMG:"/Public/Manager/images/upload_pic.png"
}

$(function() {
    /* list operate */
    /* search */
    if($(".dSearch").length) {
        $("#searchBtn").click(function() {
            $("#dSearch").submit();
        })
        $("#exportBtn").click(function() {
            $("#dSearch").attr("method", "post").submit();
        })
    }

    /* list */
    if($(".dList").length) {
        /* li style */
        $(".dList ul li").each(function() {
            var width = $(this).attr("data-width");
            var paddingL = $(this).attr("data-paddingL");
            $(this).css({"width" : width + "%", "padding-left" : paddingL + "%"});
        });

        /* dBatch style */
        $(".dBatch").append('<span class="cGrayAbox"></span>');
        $(".dBatch .aLinkAbox a").each(function() {
            $(".cGrayAbox").append(
                '<i class="cGray">'+ $(this).html() +'</i>'
            );
        });

        /* checkbox operate */
        $("#ckAll_list").click(function() {
            $(".dList input:checkbox").prop("checked", this.checked);
        })
        $("#ckAll_list, .dList input:checkbox").click(function() {
            var checkedNum = $("input:checkbox[name='singleID[]']:checked").length;
            if(checkedNum) {
                $(".cGrayAbox").css("display", "none");
                $(".aLinkAbox").css("display", "inline");
            } else {
                $(".aLinkAbox").css("display", "none");
                $(".cGrayAbox").css("display", "inline");
            }
            var itemID = new Array();
            $("input:checkbox[name='singleID[]']:checked").each(function() {
                itemID.push($(this).val());
            });
            $("#itemID").val(itemID);
            if($(".dList input:checkbox").length === checkedNum) {
                $("#ckAll_list").prop("checked", true);
            } else {
                $("#ckAll_list").prop("checked", false);
            }
        });

        /* itemID operate */
        $(".dList .aRead, .dList .aModify, .dList .aDelete, .dList .aStar, .dList .aUnStar").each(function() {
            $(this).click(function() {
                var singleID = $(this).parents("ul").find(".checkbox").val();
                $("#itemID").val(singleID);
                URL = $(this).attr("href");
                var operate = $(this).attr("class");
                if(operate === "aRead" || operate === "aModify") {
                    $("#form").attr("action", URL);
                    $("#form").submit();
                } else {
                    if(operate === "aDelete") {
                        if(confirm("确定要删除数据吗？")) {
                            formAjaxList("POST", URL, $("#form"));
                        }
                    } else {
                        formAjaxList("POST", URL, $("#form"));
                    }
                }
                return false;
            });
        });

        /* batch operate */
        $("#batchDel, #batchStar, #batchUnStar").click(function() {
            if ($("#itemID").val()) {
                var URL = $(this).attr("href");
                if($(this).attr("id") === "batchDel") {
                    if(confirm("确定要删除数据吗？")) {
                        formAjaxList("POST", URL, $("#form"));
                    }
                } else {
                    formAjaxList("POST", URL, $("#form"));
                }
            }
            return false;
        });

        /* page submit */
        $(".dPageBox .dPage .aSubmit").click(function() {
            $("#formPage").submit();
        })
    }

    /* edit inputTxt */
    if($(".dEdit").length) {
        $(".dEdit .inputTxt").each(function() {
            $(this).focus(function() {
                $(this).addClass("inputTxtFo").removeClass("inputTxt").removeClass("inputTxtEr");
            });
            $(this).blur(function() {
                $(this).addClass("inputTxt").removeClass("inputTxtFo").removeClass("inputTxtEr");
            });
        });
        $(".dEdit .areaA").each(function() {
            $(this).focus(function() {
                $(this).addClass("areaAFo").removeClass("areaA").removeClass("areaAEr");
            });
            $(this).blur(function() {
                $(this).addClass("areaA").removeClass("areaAFo").removeClass("areaAEr");
            });
        });
        $(".dEdit .ui-multiselect").each(function() {
            $(this).blur(function() {
                $(this).css("border-color", "#ccc");
            });
        });
    }

    /* edit submit */
    if($(".dEdit .formSub").length) {
        $(".dEdit .formSub").click(function() {
            formAjaxEdit("POST", $(this).attr("href"), $("#formEdit"));
            return false;
        });
    }

    /* controls Swfupload */
    if($(".dEdit .upLoadPicOne").length || $(".dEdit .upLoadPicAll").length) {
        /* loading js */
        $("<script/>").attr({src: ruis.STATIC_URL + "/controls/Swfupload/swfupload.js"}).appendTo("head");
        $("<script/>").attr({src: ruis.STATIC_URL + "/controls/Swfupload/swfupload.queue.js"}).appendTo("head");
        $("<script/>").attr({src: ruis.STATIC_URL + "/controls/Swfupload/handlers.js"}).appendTo("head");

        /* initSwfuploadOne */
        function initSwfuploadOne(i) {
            new SWFUpload({
                upload_url: "upload",
                flash_url : ruis.STATIC_URL + "/controls/Swfupload/swfupload.swf",

                file_size_limit : "1024", //1M
                file_types : "*.jpg;*.png;*.gif;*.bmp",
                file_types_description : "Web Image Files",
                file_queue_limit : "1",

                file_dialog_start_handler : fileDialogStart,
                file_queued_handler : fileQueued,
                file_queue_error_handler : fileQueueError,
                file_dialog_complete_handler : fileDialogComplete,
                upload_start_handler : uploadStart,
                upload_progress_handler : uploadProgress,
                upload_error_handler : uploadError,
                upload_success_handler : uploadSuccessOne,
                upload_complete_handler : uploadComplete,

                button_image_url : ruis.STATIC_URL + "/controls/Swfupload/swfUploadBtn.png",
                button_placeholder_id : "uploadBtnOne" + i,
                button_width: 100,
                button_height: 30,

                custom_settings : {
                    progressTarget : "uploadProgressOne" + i,
                    cancelButtonId : "cancelBtnOne" + i
                }
            })
        }
        $(".dEdit .upLoadPicOne").each(function(i) {
            $(this).find(".progressWrapperBg").attr("id", "uploadProgressOne" + i);
            $(this).find(".uploadBtnBg .aCancel").attr("id", "cancelBtnOne" + i);
            $(this).find(".uploadBtnBg .aUpload").append('<a id="uploadBtnOne'+ i +'"></a>');
            initSwfuploadOne(i);
        });

        /* initSwfuploadAll */
        function initSwfuploadAll(i) {
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
                upload_success_handler : uploadSuccessAll,
                upload_complete_handler : uploadComplete,

                button_image_url : ruis.STATIC_URL + "/controls/Swfupload/swfUploadBtn.png",
                button_placeholder_id : "uploadBtnAll" + i,
                button_width: 100,
                button_height: 30,

                custom_settings : {
                    progressTarget : "uploadProgressAll" + i,
                    cancelButtonId : "cancelBtnAll" + i
                }
            })
        }
        $(".dEdit .upLoadPicAll").each(function(i) {
            $(this).find(".progressWrapperBg").attr("id", "uploadProgressAll" + i);
            $(this).find(".uploadBtnBg .aCancel").attr("id", "cancelBtnAll" + i);
            $(this).find(".uploadBtnBg .aUpload").append('<a id="uploadBtnAll'+ i +'"></a>');
            initSwfuploadAll(i);
        });
    }

    /* controls Multiselect */
    if($("select").length) {
        /* loading css */
        $("<link>").attr({rel:"stylesheet", href:ruis.STATIC_URL + "/styles/jquery-ui.css"}).appendTo("head");
        $("select").each(function() {
            $(this).css("width", $(this).width() + 2);
        })
        /* loading js */
        $("<script/>").attr({src: ruis.STATIC_URL + "/scripts/jquery-ui.min.js"}).appendTo("head");
        $("<script/>").attr({src: ruis.STATIC_URL + "/controls/Multiselect/jquery.multiselect.js"}).appendTo("head");
        $("<script/>").attr({src: ruis.STATIC_URL + "/controls/Multiselect/jquery.multiselect.filter.js"}).appendTo("head");

        /* 单个 */
        $(".multiselectSingle").multiselect({
            multiple: false,
            header: false,
            selectedList: 1
        });
        /* 单个带搜索 */
        $(".multiselectSingleFilter").multiselect({
            multiple: false,
            selectedList: 1
        }).multiselectfilter();
        /* 多个带搜索 */
        $(".multiselectFilter").multiselect().multiselectfilter();
    }

    /* controls WdatePicker*/
    if($(".WdatePicker").length) {
        /* loading js */
        $("<script/>").attr({src: ruis.STATIC_URL + "/controls/My97DatePicker/WdatePicker.js"}).appendTo("head");

        $(".WdatePicker").each(function(i) {
            var operate = $(this).attr("id");
            if(operate === "startTime") {
                $(this).focus(function() {
                    $("#endTime").val("");
                    WdatePicker({});
                });
            }else if(operate === "endTime") {
                $(this).focus(function() {
                    WdatePicker({
                        minDate:$("#startTime").val()
                    });
                });
            } else {
                $(this).focus(function() {
                    WdatePicker({});
                })
            }
        })
    }

    /* controls KindEditor */
    if($(".dEdit .kindEditor").length) {
        /* loading js */
        $("<script/>").attr({src: ruis.STATIC_URL + "/controls/Kindeditor/kindeditor.js"}).appendTo("head");
        $("<script/>").attr({src: ruis.STATIC_URL + "/controls/Kindeditor/lang/zh_CN.js"}).appendTo("head");

        KindEditor.ready(function(K) {
            K.create(".kindEditor",{
                afterBlur  : function() {
                    this.sync();
                },
                uploadJson : "upload_editor",
                width      : "100%",
                height     : "400px",
                resizeType : "1"
            });
        });
    }

    /* controls ZeroClipboard */
    if($(".dList .aCopy").length) {
        /* loading js */
        $("<script/>").attr({src: ruis.STATIC_URL + "/controls/ZeroClipboard/ZeroClipboard.js"}).appendTo("head");

        function initClipboard(text, msn, sname) {
            var ZEROCLIPBOARD_SWF = ruis.STATIC_URL + "/controls/ZeroClipboard/ZeroClipboard.swf";
            ZeroClipboard.setMoviePath(ZEROCLIPBOARD_SWF);
            var clip = new ZeroClipboard.Client();
            clip.setHandCursor(true);
            clip.setText(text);
            clip.addEventListener("complete", function() {
                alPop(msn);
                setTimeout(function() {
                    alPopCl();
                }, ruis.TIME);
            });
            clip.glue(sname);
        }
        $(".dList .aCopy").each(function(i) {
            $(this).attr("id", "copy" + i);
            initClipboard($(this).attr("val"), $(this).attr("txt"), "copy" + i);
        });
    }
})

/* edit delImg */
function aDelImg(){
    $(".dEdit .aDelImg").click(function(){
        $(this).parents('li').find('.progressWrapper').hide();
        var key = $(this).parent('span').index();
        var inputPosition = $(this).parents('li').find('.upLoadInputName');
        var list = inputPosition.val().split(',');
        if(list.length > 1){
            list.splice(key, 1);
            list.join(',');
            inputPosition.val(list);
        }else{
            var defaultElement = "<span><img src='" + ruis.DEFAULT_UPLOAD_IMG + "'></span>";
            $(this).parent().parent(".showPic").html(defaultElement);
            inputPosition.val("");
        }
        $(this).parent("span").remove();
    })
}
new aDelImg();

/* formAjaxList */
function formAjaxList(type, url, form) {
    $.ajax({
        type:type,
        url:url,
        data:form.serialize(),
        dataType:"json",
        success: function(json) {
            alPop(json.msg);
            if(json.result == 1) {
                setTimeout(function() {
                    if(json.href) {
                        location.href = json.href;
                    } else {
                        location.reload();
                    }
                }, ruis.TIME);
            } else {
                setTimeout(function() {
                    alPopCl();
                }, ruis.TIME);
            }
        },
        error: function(XMLHttpRequest) {
            alPop("操作失败，失败状态码：" + XMLHttpRequest.status);
            setTimeout(function() {
                alPopCl();
            }, ruis.TIME);
        }
    });
}

/* formAjaxEdit */
function formAjaxEdit(type, url, form) {
    if($(".liError").length) {
        $(".liError").remove();
    }
    
    $.ajax({
        type:type,
        url:url,
        data:form.serialize(),
        dataType:"json",
        success:function(json) {
            if(json.result === 1) {
                alPop(json.msg);
                setTimeout(function() {
                    alPopCl();
                    if(json.href) {
                        location.href = json.href;
                    }
                }, ruis.TIME);
            } else {
                for(var i=0, l=json.formError.length; i<l; i++) {
                    $("input").each(function() {
                        if($(this).attr("name") === json.formError[i].name) {
                            $(this).removeClass("inputTxt").addClass("inputTxtEr");
                            $(this).parent().after('<li class="liError">'+json.formError[i].msg+'</li>');
                        }
                    })
                    $("textarea").each(function() {
                        if($(this).attr("name") === json.formError[i].name) {
                            if($(this).attr("name") !== "content") {
                                $(this).removeClass("areaA").addClass("areaAEr");
                            }
                            $(this).parent().after('<li class="liError">'+json.formError[i].msg+'</li>');
                        }
                    })
                    $("select").each(function() {
                        if($(this).attr("name") === json.formError[i].name) {
                            $(this).siblings(".ui-multiselect").css("border-color", "#de6e6e");
                            $(this).parent().after('<li class="liError">'+json.formError[i].msg+'</li>');
                        }
                    })
                }
            }
        },
        error:function(XMLHttpRequest) {
            alPop("操作失败，失败状态码：" + XMLHttpRequest.status);
            setTimeout(function() {
                alPopCl();
            }, ruis.TIME);
        }
    });
}

/* pop */
function alPop(txt) {
    if(!$("#alPop").length) {
        $("<div/>", {
            id:"alPop",
            text:txt,
            "class":"alPop"
        }).appendTo("body");
    } else {
        $("#alPop").html(txt);
    }
    $("#alPop").css("display", "block").css("top", $(document).scrollTop() + $(window).height() / 2 - 40);
};
function alPopCl() {
    if($("#alPop").length) {
        $("#alPop").fadeOut(200);
    }
}