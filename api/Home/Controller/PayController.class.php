<?php
namespace Home\Controller;

use Home\Model\ImgModel;
use Home\Model\OrderModel;
use Home\Model\RewardModel;
use Home\Model\UserSocialModel;

class PayController extends BaseController {

    protected $api = 'https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code';
    protected $APPID = 'wx8c69d85ada607cee';
    protected $APPSECRET = '79875eee38b1f5f4bf4657d223744020';

    public function payorder()
    {
        $rs = $this->rs_data;
        $openid = $this->getOpenid();
        $img_id = safe_string($_POST['img_id']);
        $amount = safe_string($_POST['amount']);
        if(!$img_id || !$amount){
            $rs['error'] = 1;
            $rs['msg'] = "参数错误！";
            $this->out_put($rs);
        }
        //get img
        $image = (new ImgModel())->where("id=$img_id")->find();
        if(empty($image)){
            $rs['error'] = 1;
            $rs['msg'] = "相片不存在！";
            $this->out_put($rs);
        }

        //get user
        $userSocial = new UserSocialModel();
        $user = $userSocial->where("openid='{$openid}'")->find();
        if(empty($user)){
            $data['openid'] = $openid;
            $data['social_type'] = 'weixin';
            $data['create_time'] = date("Y-m-d H:i:s");
            $userSocial->add($data);
            $user = $userSocial->where("openid='{$openid}'")->find();
        }

        //create order
        $orderM = new OrderModel();
        $orderData['order_id'] = $this->getUid();
        $orderData['order_status'] = 1;
        $orderData['amount'] = $amount;
        $orderData['remark'] = '打赏';
        $orderData['user_social_id'] = $user['user_social_id'];
        $orderData['create_time'] = date("Y-m-d H:i:s");
        $orderM->add($orderM);

        if(false === $orderM->add($orderM)){
            $rs['error'] = 1;
            $rs['msg'] = "订单创建失败！";
            $this->out_put($rs);
        }
        vendor('wxpaybak.example.jsapi');
        $wxpay = new \wxpay();
        $jsApiParameters = $wxpay->getXcxApiParameters($openid, $orderData);
        debug_log('jsApiParameters'."\n".json_encode($jsApiParameters));
        if(empty($jsApiParameters)){
            $rs['error'] = 1;
            $rs['msg'] = "支付失败！";
            $this->out_put($rs);
        }

        //add reward
        $rewardM = new RewardModel();
        $rewardData['order_id'] = $orderData['order_id'];
        $rewardData['img_id'] = $img_id;
        $rewardData['album_id'] = $image['album_id'];
        $rewardData['album_id'] = $image['album_id'];
        $rewardData['reward_amount'] = $amount;
        $rewardData['user_social_id'] = $user['user_social_id'];
        $rewardData['create_time'] = date("Y-m-d H:i:s");
        if(false === $rewardM->add($rewardData)){
            $rs['error'] = 1;
            $rs['msg'] = "打赏记录创建失败！";
            $this->out_put($rs);
        }

        $rs['error'] = 0;
        $rs['msg'] = "ok";
        $rs['data'] = $jsApiParameters;

        $this->out_put($rs);
    }

    public function getUid()
    {
        $id = null;
        do {
            $id = substr(strtoupper(uniqid('', true)), 0, 14);
            $isUnique = (new OrderModel())->where("order_id='{$id}'")->find();
        }while(!empty($isUnique));

        return $id;
    }

    public function pay_notify(){
        debug_log('pay_notify'."\n".file_get_contents('php://input'));
        vendor('wxpaybak.example.notify');
    }
}