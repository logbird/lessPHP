<?php
/**
 * 数据库操作类
 */
class sys_database{
	private $Host;
	private $User;
	private $Password;
	private $DB;
	private $dbCharSet;
	private $Link_ID=0;				//数据库连接
	private $Query_ID=0;			//查询结果
	private $Row_Result = array();	//结果集成组的数组
	private $Affected_row;			//影响行数
	private $Rows;					//结果集中记录的行数
	public $debug;
	public $errReport;

	//初始化数据配置
	function __construct($hostname,$username,$password,$db,$dbcharset, $debug = false, $errReport = true)
    {
		$this->Host=$hostname;
		$this->User=$username;
		$this->Password=$password;
		$this->DB=$db;
		$this->dbCharSet=$dbcharset;
		$this->debug = $debug;
        $this->errReport = $errReport;
	}

	//连接数据库
	private function connect()
    {
		if(0 == $this->Link_ID)
		{
			$this->Link_ID=mysql_connect($this->Host,$this->User,$this->Password);
			if(!$this->Link_ID)
			{
				$this->halt("连接数据库服务端失败!");
			}
			if(!mysql_select_db($this->DB,$this->Link_ID))
			{
				$this->halt("无法选择数据库".$this->DB);
			}
			mysql_query("SET NAMES '".$this->dbCharSet. "'");
		}
	}
	//执行sql语句
	private function query($Query_string)
    {
		if($this->Query_ID)
        {
			$this->free();
		}
		if(0 == $this->Link_ID)
        {
			$this->connect();
		}
		$this->Query_ID=mysql_query($Query_string, $this->Link_ID);
        if($this->debug)
        {
            echo "SQL：" . $Query_string, " ------- Status: ".intval(!!$this->Query_ID)." <br />";
        }
		if(!$this->Query_ID)
        {
		    $this->halt('SQL执行失败（'.mysql_error($this->Link_ID).':'.mysql_errno($this->Link_ID).'）'."<br >".$Query_string);
        }
		return $this->Query_ID;
	}
    //执行其他语句
    public function execute($sql)
    {
        return $this->query($sql);
    }

	//释放内存
	function free()
    {
		if(@mysql_free_result($this->Query_ID))
		{
			unset($this->Row_Result);   //释放由结果集组成的数组
		}
		$this->QueryID=0;
	}

	//打印错误信息
	function halt($msg)
	{
        if(!$this->errReport)return false;
        try
        {
		    throw new sys_exception($msg);
        }catch(sys_exception $ce)
        {
            $ce->showMsg();
        }
	}

    //释放资源
	function close()
	{
		if(0 != $this->Link_ID && is_resource($this->Link_ID))
		{
			mysql_close($this->Link_ID);
		}
	}
	function __destruct()
	{
		$this->close();
	}


	function add($arr, $table)
	{
		if(empty($arr) || empty($table))
		{
			$this->halt('增加数据时，传入数据为空！');
			return false;
		}
		$sql = 'INSERT INTO `'.$table.'`';
		$cols = '';
		$values = '';
		//$cols = '`'.implode('`,`', array_keys((array)$arr)).'`';
		foreach($arr as $k=>$v)
		{
			$cols[] = '`'.$k.'`';
			$values[] = "'".$v."'";
		}
		$sql .= '('.implode(',', $cols).') values('.implode(',',$values).')';	
		$res = $this->query($sql);
		$last_id = mysql_insert_id();
		if($last_id != 0)
		{
			return $last_id;
		}
		else
		{
			return false;
		}
	}

	function update($arr, $table, $where ='')
	{
		if(empty($arr) || empty($table))
		{
			$this->halt('更新数据时，传入数据为空！');
			return false;
		}
		if(empty($where))
		{
			$where = ' true ';
		}
		$sql = 'UPDATE `'.$table.'` SET ';

		$values = '';

		foreach($arr as $k=>$v)
		{
			$val = '`'.$k.'`'.' = ';
			$val .= "'".$v."'";
			$values[] = $val;
		}
		$sql .= implode(',', $values).' WHERE ' . $where;
		$res = $this->query($sql);
		if(mysql_affected_rows() > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	/**
	 * 获取全部数据返回一个二维数组
	 */
	function getAll($sql)
	{
		$rescouce = $this->query($sql);
		$data = array();
		if(!$rescouce)return false;
		while($row = mysql_fetch_array($rescouce, MYSQL_ASSOC))
		{
			$data[] = $row;
		}
		if(is_array($data) && !empty($data))
		{
			return $data;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * 获取一个一行一列的值 key参数为字段名
	 */
	function getOne($sql, $key)
	{
		$rescouce = $this->query($sql);
		if(!$rescouce)return false;
		$res = mysql_fetch_array($rescouce, MYSQL_ASSOC);
		if(is_array($res) && !empty($res))
		{
			return $res[$key];
		}
		else
		{
			return FALSE;
		}
	}
	/**
	 * 返回一行数据
	 */
	function getRow($sql)
	{
		$rescouce = $this->query($sql);
		if(!$rescouce)return false;
		$res = mysql_fetch_array($rescouce, MYSQL_ASSOC);
		if(is_array($res) && !empty($res))
		{
			return $res;
		}
		else
		{
			return FALSE;
		}
	}
}
?>
