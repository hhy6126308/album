<?php
/**
 * 
 * @author hhy
 * @createTime 2017-10-30 上午11:45:37
 */
class GoodsclsModel extends Model{
	protected $trueTableName = "iq_app_goodscls";
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