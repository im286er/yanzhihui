function cancelQueue(instance) {
    document.getElementById(instance.customSettings.cancelButtonId).disabled = true;
    instance.stopUpload();
    var stats;
    do {
        stats = instance.getStats();
        instance.cancelUpload();
    } while (stats.files_queued !== 0);
}

function fileDialogStart() {}

function fileQueued(file) {
    if(this.customSettings.progressTarget){
        var progress = new FileProgress(file, this.customSettings.progressTarget);
        progress.setStatus("等待中...");
        progress.toggleCancel(true, this);
    }
}

function fileQueueError(file, errorCode, message) {
    if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
        alPop("上传的队列文件太多");
        setTimeout(function() {
            alPopCl();
        }, ruis.TIME);
        return;
    }

    var progress = new FileProgress(file, this.customSettings.progressTarget);
    progress.setError();
    progress.toggleCancel(false);

    switch (errorCode) {
        case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
            progress.setStatus("上传的文件太大");
            break;
        case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
            progress.setStatus("无法上传0字节的文件");
            break;
        case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
            progress.setStatus("无效的文件类型");
            break;
        case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
            alPop("上传的队列文件太多");
            setTimeout(function() {
                alPopCl();
            }, ruis.TIME);
            break;
        default:
            if (file !== null) {
                progress.setStatus("未知错误");
            }
            break;
    }
}

function fileDialogComplete(numFilesSelected, numFilesQueued) {
    if (this.getStats().files_queued > 0) {
        if(this.customSettings.cancelButtonId){
            document.getElementById(this.customSettings.cancelButtonId).disabled = false;
        }
    }
    this.startUpload();
}

function uploadStart(file) {
    if(this.customSettings.progressTarget){
        var progress = new FileProgress(file, this.customSettings.progressTarget);
        progress.setStatus("上传中...");
        progress.toggleCancel(true, this);
    }
    return true;
}

function uploadProgress(file, bytesLoaded, bytesTotal) {
    var percent = Math.ceil((bytesLoaded / file.size) * 100);
    if(this.customSettings.progressTarget){
        var progress = new FileProgress(file, this.customSettings.progressTarget);
        progress.setProgress(percent);
        progress.setStatus("上传中...");
        progress.toggleCancel(true, this);
    }
}

function uploadSuccessOne(file, serverData) {
    var result = $.parseJSON(serverData);
    if(result.result == '1'){ //成功
        addImage(result.msg, this.customSettings.progressTarget);
    }else{
        alPop(result.msg);
        setTimeout(function() {
            alPopCl();
        }, ruis.TIME);
    }
}

function uploadSuccessAll(file, serverData) {
    var result = $.parseJSON(serverData);
    if(result.result == '1'){ //成功
        var fileMsg = result.msg;
        var progress = new FileProgress(file, this.customSettings.progressTarget);
        progress.setComplete();
        progress.setStatus(fileMsg);
        progress.toggleCancel(false);
        addImageAll(fileMsg, this.customSettings.progressTarget);
        $('.uploadBtnBg').css('margin-top', '10px');
    }
}

function addImage(src, serverData){
    var srcUrl = '/Uploads/Temp/' + src;
    var newElement = "<span><img src='" + srcUrl + "' height='100'><a href='javascript:void(0)' class='aDelImg'></a></span>";
    $('#'+ serverData).siblings('.showPic').html(newElement);
    $('#'+ serverData).siblings('.upLoadInputName').val(src);
}
function addImageAll(src, serverData){
    var srcUrl = '/Uploads/Temp/' + src;
    var newElement = "<span><img src='" + srcUrl + "'><a href='javascript:void(0)' class='aDelImg'></a></span>";
    var defaultVal = $('#'+ serverData).siblings('.upLoadInputName').val();
    if(defaultVal){
        $('#'+ serverData).siblings('.showPic').append(newElement);
    }else{
        $('#'+ serverData).siblings('.showPic').html(newElement);
    }
    var valInput = $('#'+ serverData).siblings('.upLoadInputName').val();
    if(valInput){ src = valInput + ',' + src;}
    $('#'+ serverData).siblings('.upLoadInputName').val(src);
}

function uploadComplete(file) {
    if (this.getStats().files_queued === 0) {
        var progress = new FileProgress(file, this.customSettings.progressTarget);
        $('.progressName').css('display','none');
        progress.setComplete();
        progress.setStatus("所有文件上传完成！");
        progress.toggleCancel(false);
        new aDelImg();
    } else {
        this.startUpload();
    }
}

function uploadError(file, errorCode, message) {
    var progress = new FileProgress(file, this.customSettings.progressTarget);
    progress.setError();
    progress.toggleCancel(false);

    switch (errorCode) {
        case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
            progress.setStatus("上传错误" + message);
            break;
        case SWFUpload.UPLOAD_ERROR.MISSING_UPLOAD_URL:
            progress.setStatus("配置错误");
            break;
        case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
            progress.setStatus("上传失败");
            break;
        case SWFUpload.UPLOAD_ERROR.IO_ERROR:
            progress.setStatus("服务器(IO)错误");
            break;
        case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
            progress.setStatus("安全性错误");
            break;
        case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
            progress.setStatus("上传限制");
            break;
        case SWFUpload.UPLOAD_ERROR.SPECIFIED_FILE_ID_NOT_FOUND:
            progress.setStatus("未找到文件");
            break;
        case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
            progress.setStatus("上传验证失败");
            break;
        case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
            if (this.getStats().files_queued === 0) {
                document.getElementById(this.customSettings.cancelButtonId).disabled = true;
            }
            progress.setStatus("取消");
            progress.setCancelled();
            progress.toggleCancel(false);
            break;
        case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
            progress.setStatus("停止");
            progress.toggleCancel(true);
            break;
        default:
            progress.setStatus("Unhandled Error: " + error_code);
            break;
    }
}

function FileProgress(file, targetID) {
    this.fileProgressID = file.id;
    this.height = 0;

    this.fileProgressWrapper = document.getElementById(this.fileProgressID);
    if (!this.fileProgressWrapper) {
        this.fileProgressWrapper = document.createElement("div");
        this.fileProgressWrapper.className = "progressWrapper";
        this.fileProgressWrapper.id = this.fileProgressID;

        this.fileProgressElement = document.createElement("div");
        this.fileProgressElement.className = "progressContainer";

        var progressCancel = document.createElement("a");
        progressCancel.className = "progressCancel";
        progressCancel.href = "#";
        progressCancel.style.visibility = "hidden";
        progressCancel.appendChild(document.createTextNode(" "));

        var progressText = document.createElement("div");
        progressText.className = "progressName";
        progressText.appendChild(document.createTextNode(file.name));

        var progressBar = document.createElement("div");
        progressBar.className = "progressBarInProgress";

        var progressStatus = document.createElement("div");
        progressStatus.className = "progressBarStatus";
        progressStatus.innerHTML = "&nbsp;";

        this.fileProgressElement.appendChild(progressCancel);
        this.fileProgressElement.appendChild(progressText);
        this.fileProgressElement.appendChild(progressStatus);
        this.fileProgressElement.appendChild(progressBar);

        this.fileProgressWrapper.appendChild(this.fileProgressElement);

//		document.getElementById(targetID).appendChild(this.fileProgressWrapper);
        $('#' + targetID).html(this.fileProgressWrapper);

    } else {
        this.fileProgressElement = this.fileProgressWrapper.firstChild;
        this.fileProgressElement.childNodes[1].firstChild.nodeValue = file.name;
    }
    this.height = this.fileProgressWrapper.offsetHeight;
}

FileProgress.prototype.setProgress = function (percentage) {
    this.fileProgressElement.className = "progressContainer green";
    this.fileProgressElement.childNodes[3].className = "progressBarInProgress";
    this.fileProgressElement.childNodes[3].style.width = percentage + "%";
};
FileProgress.prototype.setComplete = function () {
    this.fileProgressElement.className = "progressContainer blue";
    this.fileProgressElement.childNodes[3].className = "progressBarComplete";
    this.fileProgressElement.childNodes[3].style.width = "";
};
FileProgress.prototype.setError = function () {
    this.fileProgressElement.className = "progressContainer red";
    this.fileProgressElement.childNodes[3].className = "progressBarError";
    this.fileProgressElement.childNodes[3].style.width = "";
};
FileProgress.prototype.setCancelled = function () {
    this.fileProgressElement.className = "progressContainer";
    this.fileProgressElement.childNodes[3].className = "progressBarError";
    this.fileProgressElement.childNodes[3].style.width = "";
};
FileProgress.prototype.setStatus = function (status) {
    this.fileProgressElement.childNodes[2].innerHTML = status;
};

// Show or  Hide the cancel button
FileProgress.prototype.toggleCancel = function (show, swfUploadInstance) {
    this.fileProgressElement.childNodes[0].style.visibility = show ? "visible" : "hidden";
    if (swfUploadInstance) {
        var fileID = this.fileProgressID;
        this.fileProgressElement.childNodes[0].onclick = function () {
            swfUploadInstance.cancelUpload(fileID);
            return false;
        };
    }
};