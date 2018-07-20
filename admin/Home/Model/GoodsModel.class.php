<?php
namespace Home\Model;
use Think\Model;
class GoodsModel extends Model
{
    protected $tableName = 'wm_tb_goods';

    public function getGoodsList ($uid) {
        if (empty($uid)) return false;
        return $this->where(" g_uid = $uid ")->join("g left join wm_tb_goodscls c on g_cid = c.id ")->field("g.*,c.g_cls_name")->select();
    }

}