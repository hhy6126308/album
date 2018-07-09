<?php
class CoreModel extends Model {
	/**
	 * 执行SQL语句
	 * @access public
	 * @param string $sql  SQL指令
	 * @param mixed $parse  是否需要解析SQL
	 * @return false | integer
	 */
	public function execute($sql,$parse=false) {
		if(!is_bool($parse) && !is_array($parse)) {
			$parse = func_get_args();
			array_shift($parse);
		}
		$sql  =   $this->parseSql($sql,$parse);
		$rs = $this->db->execute($sql);
        $this->insLogSql($sql,$rs);
		return $rs;
	}
	
	/**
	 * SQL查询
	 * @access public
	 * @param string $sql  SQL指令
	 * @param mixed $parse  是否需要解析SQL
	 * @return mixed
	 */
	public function query($sql,$parse=false) {
		if(!is_bool($parse) && !is_array($parse)) {
			$parse = func_get_args();
			array_shift($parse);
		}
		$sql  =   $this->parseSql($sql,$parse);
		$rs =  $this->db->query($sql);
        $this->insLogSql($sql,$rs);
		return $rs;
	}
	
	/**
	 * 保存数据
	 * @access public
	 * @param mixed $data 数据
	 * @param array $options 表达式
	 * @return boolean
	 */
	public function save($data='',$options=array()) {
		if(empty($data)) {
			// 没有传递数据，获取当前数据对象的值
			if(!empty($this->data)) {
				$data           =   $this->data;
				// 重置数据
				$this->data     =   array();
			}else{
				$this->error    =   L('_DATA_TYPE_INVALID_');
				return false;
			}
		}
		// 数据处理
		$data       =   $this->_facade($data);
		// 分析表达式
		$options    =   $this->_parseOptions($options);
		if(false === $this->_before_update($data,$options)) {
			return false;
		}
		if(!isset($options['where']) ) {
			// 如果存在主键数据 则自动作为更新条件
			if(isset($data[$this->getPk()])) {
				$pk                 =   $this->getPk();
				$where[$pk]         =   $data[$pk];
				$options['where']   =   $where;
				$pkValue            =   $data[$pk];
				unset($data[$pk]);
			}else{
				// 如果没有任何更新条件则不执行
				$this->error        =   L('_OPERATION_WRONG_');
				return false;
			}
		}
		$result     =   $this->db->update($data,$options);
		$sql = $this->getLastSql();
        $this->insLogSql($sql,$result);
		if(false !== $result) {
			if(isset($pkValue)) $data[$pk]   =  $pkValue;
			$this->_after_update($data,$options);
		}
		return $result;
	}
	
	/**
	 * 删除数据
	 * @access public
	 * @param mixed $options 表达式
	 * @return mixed
	 */
	public function delete($options=array()) {
		if(empty($options) && empty($this->options['where'])) {
			// 如果删除条件为空 则删除当前数据对象所对应的记录
			if(!empty($this->data) && isset($this->data[$this->getPk()]))
				return $this->delete($this->data[$this->getPk()]);
			else
				return false;
		}
		if(is_numeric($options)  || is_string($options)) {
			// 根据主键删除记录
			$pk   =  $this->getPk();
			if(strpos($options,',')) {
				$where[$pk]     =  array('IN', $options);
			}else{
				$where[$pk]     =  $options;
			}
			$pkValue            =  $where[$pk];
			$options            =  array();
			$options['where']   =  $where;
		}
		// 分析表达式
		$options =  $this->_parseOptions($options);
		$result=    $this->db->delete($options);
		$sql = $this->getLastSql();
        $this->insLogSql($sql,$result);
		if(false !== $result) {
			$data = array();
			if(isset($pkValue)) $data[$pk]   =  $pkValue;
			$this->_after_delete($data,$options);
		}		
		// 返回删除记录个数
		return $result;
	}
	
	/**
	 * 新增数据
	 * @access public
	 * @param mixed $data 数据
	 * @param array $options 表达式
	 * @param boolean $replace 是否replace
	 * @return mixed
	 */
	public function add($data='',$options=array(),$replace=false) {
		if(empty($data)) {
			// 没有传递数据，获取当前数据对象的值
			if(!empty($this->data)) {
				$data           =   $this->data;
				// 重置数据
				$this->data     = array();
			}else{
				$this->error    = L('_DATA_TYPE_INVALID_');
				return false;
			}
		}
		// 分析表达式
		$options    =   $this->_parseOptions($options);
		// 数据处理
		$data       =   $this->_facade($data);
		if(false === $this->_before_insert($data,$options)) {
			return false;
		}
		// 写入数据到数据库
		$result = $this->db->insert($data,$options,$replace);
		$sql = $this->getLastSql();
        $this->insLogSql($sql,$result);
		if(false !== $result ) {
			$insertId   =   $this->getLastInsID();
			if($insertId) {
				// 自增主键返回插入ID
				$data[$this->getPk()]  = $insertId;
				$this->_after_insert($data,$options);
				return $insertId;
			}
			$this->_after_insert($data,$options);
		}
		return $result;
	}
	
	protected function _before_insert(&$data,$options) {}

    /**
     * 写入sql 日志
     * @param  varchar $sql sql语句
     * @param  $result 结果集
     * @return boolean
     */
	public  function insLogSql($sql,$result){
	    if(empty($sql) || stristr($sql,'select'))  return false;
        @mwtlog("sql_log",session('user')." : ".$sql."  res:".json_encode($result),true);
    }
}