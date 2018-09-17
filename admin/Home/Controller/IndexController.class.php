<?php
namespace Home\Controller;

class IndexController extends BaseController {

    public function _initialize(){
        layout("Comon/layout");
        $this->checkAuth();
        $this->assign('sidebar_name','index');
    }

    public function index(){
        $user_type = session("ADMIN_TYPE");
        $url = $user_type > 10 ? '/Album' : '/AlbumGroup';
        redirect($url);
        //$this->display('index');
    }
}