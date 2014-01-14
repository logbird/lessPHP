<?php
/**
 * pdo数据库操作类
 *
 * 该类暂未完成 BindValue的封装
 * @version LessPHP 1.0
 * @author logbird <logbird@126.com>
 * @license BSD {@link http://www.opensource.org/licenses/bsd-license.php
 */
class sys_pdodb {

    /*******************************数据库连接配置区域*****************************/
    private $setting;
    private $dbname;
    private $charset;

    /**
     * 是否开启调试模式
     *
     * @var Boolean
     */
    private $debug;

    /**
     * debug信息输入文件，如果为空则 直接输出到页面中 debug 为 True 时生效
     *
     * @var string
     */
    private $debugFile = '';

    /**
     * 是否提示MySQL错误 关闭该选项后 如果出现错误则会 返回FALSE
     *
     * @var Boolean
     */
    private $errReport;

    /**
     * 是否已经开启事务
     *
     * @var Boolean
     */
    private $isTrans = false;

    private $db = array();
    private $curdb = "master";
    private $dns = '';
    private $driverOpt = array();

    /**
     * 构造函数
     *
     * @param Array $config
     * @param Boolean $debug
     * @param Boolean $errReport
     * @return void
     */
    public function __construct($config, $debug = false, $errReport = true)
    {

        if(empty($config))
            $this->halt("配置文件传入有误");
        if(!isset($config['master']) && $config['master'])
            $this->halt("请至少传入一个master库的配置信息");

        $setting = array();
        $setting['master'] = $config['master'];
        $setting['slave'] = isset($config['slave']) ? $config['slave'] : array();

        //设置主库DSN串
        $setting['master']['dsn'] = $this->_getDSN($config['master']['host'], $config['master']['port'], $config['master']['dbname']);
        //设置从库DSN串
        if(!empty($setting['slave']) && is_array($setting['slave']))
        {
            $slave = $setting['slave'];
            if(!isset($slave['host']) || !isset($slave['uname']) || !isset($slave['upwd']))
            {
                $slave = array_rand($setting['slave']);
            }
            $setting['slave']['dsn'] = $this->_getDSN($config['slave']['host'], $config['slave']['port'], $config['slave']['dbname']);
        }

        //设置编码
        $this->charset = isset($config['charset']) ? $config['charset'] : 'utf8';

        //PDO选项
        if(isset($config['driverOpt']))
            $setting['driverOpt'] = $config['driverOpt'];

        $this->setting = $setting;

        $this->debug = $debug;
        $this->errReport = $errReport;

        if(!isset($this->driverOpt[PDO::ATTR_CASE]))
        {
            $this->driverOpt[PDO::ATTR_CASE] = PDO::CASE_LOWER;
        }
    }

    /**
     * 根据配置文件获得DSN串
     *
     * @param Array $config
     * @param String $dbname
     * @return void
     */
    private function _getDSN($host, $port, $dbname)
    {
        $dsn = "%s:host=%s;port=%s;dbname=%s";
        $dsn = sprintf($dsn, 'mysql', $host, $port, $dbname);
        return $dsn;
    }

    /**
     * 连接数据库
     *
     * @param String $change 选择主从数据库 master/slave 默认为master
     * @return void
     */
    private function _connect($change)
    {
        if(empty($this->setting['slave']))
        {
            $linkInfo = $this->setting['master'];
        }else
        {
            $linkInfo = $this->setting[$change];
        }
        if(isset($this->db[$change]) && $this->db[$change] != null)
        {
            $this->debug("使用缓存连接".$change."库");
            return $this->db[$change];
        }

        $this->db[$change] = new PDO($linkInfo['dsn'], $linkInfo['uname'], $linkInfo['upwd'], $this->driverOpt);
        if(!$this->db[$change])
            $this->halt("数据库连接失败！");

        $this->debug("连接".$change."库成功，连接信息：".json_encode($linkInfo));
        $this->debug("PDO配置信息：".json_encode($this->driverOpt));

        $this->db[$change]->exec("SET NAMES " . $this->charset);
        return $this->db[$change];
    }

    /**
     * 获取pdo对象
     *
     * @param String $change 选择主从数据库 master/slave 默认为master
     * @return void
     */
    public function db($change = 'master')
    {
        $this->curdb = $change;
        return $this->_connect($change);
    }

    /**
     * query执行
     *
     * @param String $sql
     * @param String $change 选择主从数据库 master/slave 默认为master
     * @return void
     */
    private function _query($sql, $change = 'master')
    {
        if(empty($sql))
            $this->halt("请传入正确的SQL语句");

        //测试执行时间
        $runTime = microtime(true);

        $stmt = $this->db($change)->query($sql);
        if(!$stmt)
            $this->halt("SQL语句执行失败：".$sql);

        $this->debug("从".$change."库执行SQL：".htmlspecialchars($sql) . "&nbsp;执行结果:".(!$stmt?'Failed':'OK') . "执行时间:".(microtime(true)-$runTime));
        return $stmt;
    }

    /**
     * 获取最后执行的id
     *
     * @return void
     */
    public function get_lastid()
    {
        return $this->db()->lastInsertId();
    }

    /**
     * 获取全部数据
     *
     * @param String $sql
     * @param String $change 选择主从数据库 master/slave 默认为slave
     * @return void
     */
    public function getAll($sql, $change = 'slave')
    {
        $stmt = $this->_query($sql, $change);
        if(!$stmt)
            return false;
        $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$r = is_array($r) ? $r : array();
        return $r;
    }

    /**
     * 获取一行数据
     *
     * @param String $sql
     * @param String $change 选择主从数据库 master/slave 默认为slave
     * @return void
     */
    public function getRow($sql, $change = 'slave')
    {
        $stmt = $this->_query($sql, $change);
        if(!$stmt)
            return false;
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
		$r = is_array($r) ? $r : array();
        return $r;
    }

    /**
     * 获取一行一列值
     *
     * @param String $sql
     * @param String $key
     * @param String $change 选择主从数据库 master/slave 默认为slave
     * @return void
     */
    public function getOne($sql, $key, $change = 'slave')
    {
        $stmt = $this->_query($sql, $change);
        if(!$stmt)
            return false;
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        $r = isset($r[$key]) ? $r[$key] : false;
        return $r;
    }

    /**
     * 插入一条数据
     *
     * @param Array $arr
     * @param String $table
     * @return void
     */
    public function add($arr, $table)
    {
        $sql = 'INSERT INTO `'.$table.'`';
		$cols = '';
		$values = '';
		foreach($arr as $k=>$v)
		{
			$cols[] = '`'.$k.'`';
			$values[] = "'".$v."'";
		}
		$sql .= '('.implode(',', $cols).') values('.implode(',',$values).')';	

		$stmt = $this->_query($sql, 'master');
        if(!$stmt)
            return false;
		$last_id = $this->get_lastid();
		if($last_id != 0)
		{
            $this->debug("输入写入成功，返回主健为：".$last_id);
			return $last_id;
		}
		else
		{
			return false;
		}
    }

	/**
     * Replace一条数据
     *
     * @param Array $arr
     * @param String $table
     * @return void
     */
    public function replace($arr, $table)
    {
        $sql = 'REPLACE INTO `'.$table.'`';
		$cols = '';
		$values = '';
		foreach($arr as $k=>$v)
		{
			$cols[] = '`'.$k.'`';
			$values[] = "'".$v."'";
		}
		$sql .= '('.implode(',', $cols).') values('.implode(',',$values).')';	

		$stmt = $this->_query($sql, 'master');
        if(!$stmt)
            return false;
		if($last_id != 0)
		{
            $this->debug("输入写入成功，返回主健为：" . $last_id);
			return true;
		}
		else
		{
			return false;
		}
    }

    /**
     * 批量插入数据
     *
     * @param Array $batch
     * @param String $table
     * @return void
     */
    public function addBatch($batch, $table)
    {
        $sql = 'INSERT INTO `'.$table.'`';
		$sql .= '(`'.implode('`,`', array_keys((array)$batch[0])).'`) VALUES ';
		$values = array();
		foreach($batch as $row)
		{
		    $values[] = "('".implode("','", array_values((array)$row))."')";
		}
		$sql .= implode(',', $values);
		$stmt = $this->_query($sql, 'master');
        if(!$stmt)
            return false;
        $rowCount = $stmt->rowCount();
        $this->debug("批量插入影响行数为：".$rowCount);
        return $rowCount;
    }

    /**
     * 编辑数据
     *
     * @param Array $arr
     * @param String $table
     * @param String $where
     * @return void
     */
    public function update($arr, $table, $where ='')
    {
        if(empty($arr) || empty($table))
		{
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
        $stmt = $this->_query($sql, 'master');
        if(!$stmt)
            return false;
        $rowCount = $stmt->rowCount();
        $this->debug("更新数据影响行数为：".$rowCount);
        return $rowCount;
    }

    /**
     * 关于事务方面建议使用这个函数
     *
     * @param mixed $callback 回调函数 如果 该回调是某个类的方法 则传入 array($object, $methodName)
     * @param Array $args 回调函数的参数
     * @return void
     */
    public function transaction($callback, $args = array())
    {
        $this->startTrans();
        try{
            if(is_array($callback))
            {
                $obj = $callback[0];
                $method = $callback[1];
                $obj->$method($args);
            }else
            {
                $callback($args);
            }
            return true;
        }catch(Exception $ce)
        {
            $this->rollbackTrans();
            return false;
        }
        $this->commitTrans();
    }

    /**
     * 开始事务
     *
     * @return void
     */
    public function startTrans()
    {
        if(!$this->isTrans)
        {
            $this->debug("-------------事务开始-------------");
            $this->db()->beginTransaction();
            $this->isTrans = true;
        }
    }

    /**
     * 回滚事务
     *
     * @return void
     */
    public function rollbackTrans()
    {
        if($this->isTrans)
        {
            $this->debug("-------------事务回滚-------------");
            $this->db()->rollBack();
            $this->isTrans = false;
        }
    }

    /**
     * 提交事务
     *
     * @return void
     */
    public function commitTrans()
    {
        if($this->isTrans)
        {
            $this->debug("-------------事务提交-------------");
            $this->db()->commit();
            $this->isTrans = false;
        }
    }

    /**
     * 获取当前数据库所有表名
     *
     * @return void
     */
    public function getTableList()
    {
        $sql = "SHOW TABLES";
        $table = $this->getAll($sql);
        foreach($table as $k => $v)
        {
            $table[$k] = current($v);
        }
        return $table;
    }

    /**
     * 获取某个表的字段的信息
     *
     * @param String $table
     * @param String $dbname
     * @return void
     */
    public function getCols($table, $dbname = '')
    {
        $dbname = empty($dbname) ? $this->dbname : $dbname;
        if(empty($dbname))
        {
            return false;
        }
        $sql = "SELECT 
            COLUMN_NAME, DATA_TYPE, IF(ISNULL(CHARACTER_MAXIMUM_LENGTH), (NUMERIC_PRECISION + NUMERIC_SCALE), CHARACTER_MAXIMUM_LENGTH) AS MAXCHAR, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA, COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".$table."' AND TABLE_SCHEMA='".$dbname."'";
        $cols = $this->getAll($sql);
        return $cols;
    }

    /**
     * 设置PDO属性
     *
     * @param String $attr
     * @param String $value
     * @param String $change 选择主从数据库 master/slave 默认为master
     * @return void
     */
    public function setAttr($attr, $value, $change = 'master')
    {
        $this->debug("设置PDO属性:".$attr." 值:".$value);
        $this->db($change)->setAttribute($attr, $value);
    }

    /**
     * 获取PDO属性值
     *
     * @param String $attr
     * @param String $change 选择主从数据库 master/slave 默认为master
     * @return void
     */
    public function getAttr($attr, $change = 'master')
    {
        $this->db($change)->getAttribute($attr);
    }

    /**
     * 释放调所有MySQL链接
     *
     * @param String $change 选择主从数据库 master/slave 默认为master
     * @return void
     */
    public function free($change = '')
    {
        if($change == '')
        {
            foreach($this->db as $k => $v)
            {
                $this->debug("释放".$k."库连接");
                $this->db[$k] = NULL;
            }
            $this->db = array();
        }
        if(isset($this->db[$change]))
        {
            $this->db[$change] = NULL;
            $this->debug("释放".$change."库连接");
        }
    }

	/**
     * 打印错误信息
	 *
	 * @param String $msg
	 * @access public
	 * @return void
	 */
	private function halt($msg)
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

    /**
     * 输出Debug信息 如果配置了 debugFile则输出在debugFile 文件中
     *
     * @param String $msg
     * @return void
     */
    private function debug($msg)
    {
        if(!$this->debug)
            return false;
        if(!empty($this->debugFile))
        {
            if(@file_put_contents($this->debugFile, $msg."\r\n", FILE_APPEND) <= 0)
            {
                echo $this->debugFile." 无写权限，写入日志失败<br>";
            }
        }else
        {
            echo $msg, "<br>";
        }
    }

    /**
     * 设置是否开启debug
     *
     * @param Boolean $debug
     * @param String $debugFile 如果 不需要输入到文件中 则传入 空字符串
     * @return void
     */
    public function setDebug($debug = true, $debugFile = false)
    {
        $this->debug = $debug;
        if($debugFile !== false)
        {
            $this->debugFile = $debugFile;
        }
    }

    /**
     * 垃圾回收
     *
     * @access protected
     * @return void
     */
    function __destruct()
    {
        $this->free();
    }



}
