<?php
/*
* 后台游戏管理模块类
*/
class BannerModel extends Model{
	protected $trueTableName = "iq_tb_banner";
	protected $connection = 'DB_CONFIG1';
	protected $number = 20 ; 

	public function get_list($page){
		$limit = $this->number*($page - 1).",".$this->number;
		$where['isdel'] = 0 ;
		return $this->where($where)->order("id desc")->limit($limit)->select();
	}

	public function get_total_num(){
		$where['isdel'] = 0 ;
		return $this->where($where)->count();
	}
	
}