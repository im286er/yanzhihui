<?php
namespace Api\Controller;

class ApiController extends BaseController {
    /**
     * 上传文件 uploads
     */
    public function uploads() {
        if (IS_POST) {
            /* 定义变量 */
            $RESPONSE_STATUS = 500;
            /* 上传图片 */
            $resultUploads = upload_file();
            if ($resultUploads['result'] == 1)
                $RESPONSE_STATUS = 100;
            $result = array('Tips' => $resultUploads['msg'], 'RESPONSE_STATUS' => $RESPONSE_STATUS, 'RESPONSE_INFO' => $resultUploads['msg']);
            $this->ajaxReturn($result);
        }
    }
}