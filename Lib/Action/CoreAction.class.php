<?php
/**
 * 基础Action
 * @author hhy
 * @createTime 2017-10-30 上午11:39:53
 */
class GoodsAction extends Action{
	
	/**
	 * 分页
	 */
	public function core_page($page,){
		if($_GET['page']){
			$currpage = intval($_GET['page']);
		}else{
			$currpage = 1;
		}
	}
}

































