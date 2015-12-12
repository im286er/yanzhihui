<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html>
<html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        <title>颜之惠官网|一个刷脸免单的APP</title>
        <meta name="keyword" content="颜之惠,颜惠,颜值惠,颜值,颜之会,颜值会,颜之慧,颜值会，陌陌，美女，社交" />
        <meta name="description" content="·晒自拍，攒颜币：晒得越多，颜币越多。·兑颜币，获免单：10颜币＝1元，海量商家任你兑现。·看靓照，交朋友：与附近高颜值的人互动交友。" />
        <!-- External CSS -->
        <link rel="stylesheet" href="/yanzhihui/Public/yanzhihui/Home/styles/style.css">
        <!-- In-document CSS -->
    </head>
    <body>
        <!--header-->
        <div class="header">
            <div class="wrap">
                <div class="logo fl"><img src="/yanzhihui/Public/yanzhihui/Home/images/logo.png"></div>
                <div class="nav">
                    <a href="javascript:void(0)" id="contact_click">联系我们</a>
                    <a href="javascript:void(0)" id="about_click">关于我们</a>
                </div>
            </div>
        </div>
        
        <!--index-->
        <div class="index">
            <div class="wrap">
                <div class="focus fl">
                    <div class="pic">
                        <ul>
                            <li><img src="/yanzhihui/Public/yanzhihui/Home/images/focus_1.png"></li>
                            <li><img src="/yanzhihui/Public/yanzhihui/Home/images/focus_2.png"></li>
                            <li><img src="/yanzhihui/Public/yanzhihui/Home/images/focus_3.png"></li>
                        </ul>
                    </div>
                    <a class="prev" href="javascript:void(0)"></a>
                    <a class="next" href="javascript:void(0)"></a>
                    <div class="num">
                        <ul></ul>
                    </div>
                </div>
                <div class="code fl">
                    <ul>
                        <li><img src="/yanzhihui/Public/yanzhihui/Home/images/QR_code.jpg"></li>
                        <li>扫描二维码下载</li>
                    </ul>
                </div>
                <div class="btn fl">
                    <a href="https://itunes.apple.com/cn/app/yan-zhi-hui/id1050066818" class="ios" target="new_window"></a>
                    <a href="<?php echo ($vo["data_setting"]["down_link_android"]); ?>" class="android"></a>
                </div>
            </div>
        </div>
        
        <!--footer-->
        <div class="footer">
            <p>广州颜之惠信息科技有限公司 © 粤ICP备15078427号-1</p>
        </div>
        
        <!--pop-->
        <div class="pop" id="about">
            <div class="iTitle">关于我们<a href="javascript:void(0)" class="close"></a></div>
            <p class="bTitle">颜之惠APP</p>
            <p>一个刷脸免单的 社交+O2O 平台。我们坚信：美丽是才华的加速器，容颜是内涵的引路人。</p>
            <p class="bTitle">颜萨（YANSA）俱乐部</p>
            <p>由聂帅（商标所有者）从中国广州发起，灵感来自于1946年发源于英国伦敦的汇聚顶级智商拥有者的门萨（MENSA）俱乐部，颜萨聚集着国内各大城市最高颜值的绅士和女士们。美丽的人往往比普通人拥有更高的成就，颜萨俱乐部在各城市的分部通过组织线下的高颜值社交PARTY来帮助会员拓展人脉圈，并追求有品质的生活方式，分享自身故事。TA们明明可以靠脸吃饭，却兼具才华，一直不懈的提升自身价值和内涵。</p>
            <p class="bTitle">创始人聂帅</p>
            <p>清华大学流体力学本科毕业，前分众传媒创始团队成员，知名微信公众号《聂帅说》作者（公众号ID：nieshuaishuo）及知乎大V，颜值经济倡导者，粉群经济实践者，社会化营销专家。</p>
        </div>
        
        <div class="popBg"></div>
        <div class="pop" id="contact">
            <div class="iTitle">联系我们<a href="javascript:void(0)" class="close"></a></div>
            <div class="iLeft fl">
                <div class="iTit">关注颜之惠</div>
                <ul>
                    <li><img src="/yanzhihui/Public/yanzhihui/Home/images/QR_code_wb.jpg"></li>
                    <li>官方微博</li>
                </ul>
                <ul>
                    <li><img src="/yanzhihui/Public/yanzhihui/Home/images/QR_code_wx.jpg"></li>
                    <li>官方微信公众号</li>
                </ul>
            </div>
            <div class="iRight fl">
                <ul>
                    <li>商务合作 :</li>
                    <li class="cBlue">business@yanzhihui.cn</li>
                    <li>媒体采访 :</li>
                    <li class="cBlue">media@yanzhihui.cn</li>
                    <li>建议和反馈 :</li>
                    <li class="cBlue">advice@yanzhihui.cn</li>
                    <li>加入我们 :</li>
                    <li class="cBlue">hr@yanzhihui.cn</li>
                </ul>
            </div>
        </div>
        
        <!-- External JS -->
        <script src="/yanzhihui/Public/Static/scripts/jquery-1.11.3.min.js"></script>
        <script src="/yanzhihui/Public/yanzhihui/Home/scripts/jquery.superslide.2.1.1.js"></script>
        <!-- In-document JS -->
        <script>
            $(function(){
                $(".focus").slide({
                    titCell:".num ul",
                    mainCell:".pic ul",
                    effect:"fold",
                    autoPlay:true,
                    delayTime:700,
                    autoPage:true
                });

                <!--pop-->
                $('#contact_click').click(function(){
                    $('#contact, .popBg').show();
                })
                $('#about_click').click(function(){
                    $('#about, .popBg').show();
                })
                $('.close').click(function(){
                    $('.pop, .popBg').hide();
                })
            })
        </script>
    </body>
</html>