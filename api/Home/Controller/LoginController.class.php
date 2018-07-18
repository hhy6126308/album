<?php
namespace Home\Controller;

use Home\Model\UserModel;
use Home\Model\UserSocialModel;

class LoginController extends BaseController {

    protected $api = 'https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code';
    protected $APPID = 'wx8c69d85ada607cee';
    protected $APPSECRET = '79875eee38b1f5f4bf4657d223744020';
    protected $tokenTimeout = 2*60*60;

    public function wxLogin()
    {
        $rs = $this->rs_data;
        $jscode = safe_string($_POST['jscode']);
        $nick_name = safe_string($_POST['nick_name']);
        if(empty($jscode)){
            $rs['error'] = 1;
            $rs['msg'] = 'jscode为空！';
            $this->out_put($rs);
        }
        $getOpenidUrl = sprintf($this->api,$this->APPID,$this->APPSECRET,$jscode);
        $WXres = json_decode(\getData($getOpenidUrl),true);
        $openid = $WXres['openid'];
        $session_key = $WXres['session_key'];
        if(empty($openid) || empty($session_key)){
            $rs['error'] = 1;
            $rs['msg'] = $WXres['errmsg'];
            $this->out_put($rs);
        }
        $redisM = new \RedisModel();
        $rd_session = exec('head -n 80 /dev/urandom | tr -dc A-Za-z0-9 | head -c 168');
        if($rd_session && $redisM->hset("ZXHY_XCX_RDSESSION",$rd_session,json_encode($WXres))){
            //新建用户
            $userSocial = new UserSocialModel();
            $user = $userSocial->where("openid=$openid")->find();
            if(empty($user)){
                $data['openid'] = $openid;
                $data['social_type'] = 'weixin';
                $data['nick_name'] = $nick_name;
                $data['create_time'] = date("Y-m-d H:i:s");
                $userSocial->add($data);
            }

            $rs['error'] = 0;
            $rs['msg'] = 'ok';
            $rs['data'] = $rd_session;
        }else{
            $rs['error'] = 1;
            $rs['msg'] = 'session获取失败！';
            $rs['data'] = '';
        }
        $this->out_put($rs);
    }

    public function adiminLogin()
    {
        if ($_POST) {
            try {
                $data['email'] = safe_string($_POST['email']);
                $data['login_pwd'] = safe_string($_POST['login_pwd']);
                if (empty($data['email']) || empty($data['login_pwd'])) {
                    throw new \Think\Exception("请填写登录邮箱和密码！", 1);
                }

                $userM = new UserModel();
                $user = $userM->where("email = '{$data['email']}'")->find();
                if ($user) {
                    $data['last_login'] = date('Y-m-d H:i:s');
                    $data['login_pwd'] = md5(C('USER_SALT') . $data['login_pwd']);
                    if ( $user['login_pwd'] != $data['login_pwd']) {
                        throw new \Think\Exception("密码错误！", 1);
                    }
                    //过滤密码
                    unset($user['login_pwd']);
                    //设置token
                    $redisM = new \RedisModel();
                    $token = md5('token_'.$user);
                    if($redisM->exists($token) == 0){
                        $redisM->setex($token, $this->tokenTimeout);
                    }
                    $user['token'] = $token;

                    $rs['error'] = 0;
                    $rs['msg'] = 'ok';
                    $rs['data'] = $user;
                    $this->out_put($rs);
                } else {
                    throw new \Think\Exception("帐号不存在！", 1);
                }
            } catch ( \Think\Exception $e ) {
                $rs['error'] = 1;
                $rs['msg'] = $e->getMessage();;
                $rs['data'] = '';

                $this->out_put($rs);
            }
        }
    }
}