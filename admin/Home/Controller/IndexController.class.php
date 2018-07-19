<?php
namespace Home\Controller;

class IndexController extends BaseController {

    public function _initialize(){
        layout("Comon/layout");
        $this->checkAuth();
        $this->assign('sidebar_name','index');
    }

    public function index(){
        $this->display('index');
    }
}