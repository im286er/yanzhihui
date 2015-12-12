<?php
namespace Api\Model;

class SendsmsModel extends CommonModel {
    /* 关闭字段自动检测 */
const MODEL_REGISTER = 4;

    /* 插入模型数据 操作状态 */
    const MODEL_FORGET_PASSWORD = 5; //注册用户
        protected $autoCheckFields = false; //忘记密码

    /* 自动验证 */
    protected $_validate = array(
        array('telephone', 'require', '{%YZ_telephone_enter}', self::MUST_VALIDATE),
        array('telephone', 'validate_lostTime', '{%YZ_captcha_lostTime}', self::MUST_VALIDATE, 'callback'),
        /* 注册用户 */
        array('telephone', 'validate_telephone_noExist', '{%YZ_telephone_exist}', self::MUST_VALIDATE, 'callback', self::MODEL_REGISTER),
        /* 忘记密码 */
        array('telephone', 'validate_telephone_exist', '{%YZ_telephone_noExist}', self::MUST_VALIDATE, 'callback', self::MODEL_FORGET_PASSWORD)
    );

    /* 数据操作 */
    /**
     * 注册发送验证码 do_register
     * @param null $telephone
     * @return bool|int
     */
    public function do_register($telephone = NULL) {
        if ($this->create('', self::MODEL_REGISTER)) {
            /* 定义变量 */
            $code = rand('100000', '999999');
            $content = array($code, C('API_SMS.lost_time') / 60);
            $template = 36687;
            /* 手机号码，替换内容数组，模板ID */
            $resultSMS = sendTemplateSMS($telephone, $content, $template);
            if ($resultSMS->statusCode == 0)
                return $code;
        }
        return false;
    }

    /**
     * 忘记密码发送验证码 do_forget
     * @param null $telephone
     * @return bool|int
     */
    public function do_forget($telephone = NULL) {
        if ($this->create('', self::MODEL_FORGET_PASSWORD)) {
            /* 定义变量 */
            $code = rand('100000', '999999');
            $content = array($code, C('API_SMS.lost_time') / 60);
            $template = 36688;
            /* 手机号码，替换内容数组，模板ID */
            $resultSMS = sendTemplateSMS($telephone, $content, $template);
            if ($resultSMS->statusCode == 0)
                return $code;
        }
        return false;
    }

    /* 自动验证和自动完成函数 */
    /* 验证时效 validate_lost_time*/
    protected function validate_lostTime($data) {
        if ($data) {
            $type = I('get.type');
            $captchaTime = 'captchaTime_' . $data . '_' . $type;
            if (NOW_TIME - S($captchaTime) > 60 * 1) //1分钟内禁止多次获取验证码
                return true;
        }
        return false;
    }
}