<?php

namespace Home\Controller;

class UploadController extends BaseController
{
    public function _initialize()
    {

    }

    public function faceimg()
    {
        $rs = ["error" => 1, "msg" => "请上传图片", "data" => ""];
        if (!empty($_FILES)) {
            vendor("UploadFile.upload");
            $upload            = new \Vendor\UploadFile();// 实例化上传类
            $upload->maxSize   = 3145728;// 设置附件上传大小
            $upload->allowExts = ['jpg', 'gif', 'png', 'jpeg'];// 设置附件上传类型
            $upload->savePath  = C('SavePicPath') . '/';// 设置附件上传目录
            if (!$upload->upload()) {// 上传错误提示错误信息
                $rs['msg'] = $upload->getErrorMsg();
            } else {// 上传成功 获取上传文件信息
                $info        = $upload->getUploadFileInfo();
                $rs['error'] = 0;
                $rs['msg']   = "ok";
                $rs['data']  = C('SavePicUrl') . '/' . $info[0]['savename'];
            }
        }
        $this->out_put($rs);
    }

}