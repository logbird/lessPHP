<?php
/**
 * pdo数据库操作类
 *
 * 该类暂未完成 缺少读写分离的支持 多数据库的支持 还有部分需要优化的地方 异常处理有待完善 以及BindValue的封装
 * @version LessPHP 1.0
 * @author logbird <logbird@126.com>
 * @license BSD {@link http://www.opensource.org/licenses/bsd-license.php
 */
class sys_pdodb {

    /*******************************数据库连接配置区域*****************************/
    private $driver;
    private $host;
    private $dbname;
    private $user;
    private $pwd;
    private $charset;

    /**
     * 是否开启调试模式
     *
     * @var Boolean
     */
    private $debug;

    /**
     * 是否提示MySQL错误 关闭该选项后 如果出现错误则会 返回FALSE
     *
     * @var Boolean
     */
    private $errReport;

    private $db = null;
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
            throw new exception();

        //设置 DSN
        if(isset($config['dsn']))
        {
            $this->dsn = $config['dsn'];
        }else
        {
            if(!isset($config['host']) || !isset($config['dbname']))
                throw new exception();
            $this->host = $config['host'];
            $this->dbname = $config['dbname'];
            $this->driver = isset($config['driver']) ? $config['driver'] : 'mysql';
            $dsn = "%s:host=%s;dbname=%s";
            $this->dsn = sprintf($dsn, $this->driver, $this->host, $this->dbname);
        }

        $this->user = isset($config['user']) ? $config['user'] : '';
        $this->pwd = isset($config['pwd']) ? $config['pwd'] : '';
        $this->charset = isset($config['charset']) ? $config['charset'] : 'utf8';
        if(isset($config['driverOpt']))
            $this->driverOpt = $config['driverOpt'];
        $this->debug = $debug;
        $this->errReport = $errReport;

        if(!isset($this->driverOpt[PDO::ATTR_CASE]))
        {
            $this->driverOpt[PDO::ATTR_CASE] = PDO::CASE_LOWER;
        }
    }

    /**
     * 连接数据库
     *
     * @return void
     */
    private function _connect()
    {
        if($this->db != null)
        {
            return $this->db;
        }
        $this->db = new PDO($this->dsn, $this->user, $this->pwd, $this->driverOpt);
        $this->db->exec("SET NAMES " . $this->charset);
        return $this->db;
    }

    /**
     * 获取pdo对象
     *
     * @return void
     */
    public function db()
    {
        return $this->_connect();
    }

    /**
     * query执行
     *
     * @param String $sql
     * @return void
     */
    private function _query($sql)
    {
        if(empty($sql))
                throw new exception();
        $stmt = $this->db()->query($sql);
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
     * @return void
     */
    public function getAll($sql)
    {
        $stmt = $this->_query($sql);
        $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $r;
    }

    /**
     * 获取一行数据
     *
     * @param String $sql
     * @return void
     */
    public function getRow($sql)
    {
        $stmt = $this->_query($sql);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r;
    }

    /**
     * 获取一行一列值
     *
     * @param String $sql
     * @param String $key
     * @return void
     */
    public function getOne($sql, $key)
    {
        $stmt = $this->_query($sql);
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
		//$cols = '`'.implode('`,`', array_keys((array)$arr)).'`';
		foreach($arr as $k=>$v)
		{
			$cols[] = '`'.$k.'`';
			$values[] = "'".$v."'";
		}
		$sql .= '('.implode(',', $cols).') values('.implode(',',$values).')';	

		$stmt = $this->_query($sql);
		$last_id = $this->get_lastid();
		if($last_id != 0)
		{
			return $last_id;
		}
		else
		{
			return false;
		}
    }

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
		$stmt = $this->_query($sql);
        return $stmt->rowCount();
    }

    public function edit($arr, $table, $where ='')
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
		$res = $this->query($sql);
        $stmt = $this->_query($sql);
        return $stmt->rowCount();
    }

    //关于事务方面建议使用这个函数
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
        }catch(Exception $ce)
        {
            $this->rollbackTrans();
        }
        $this->commitTrans();
    }

    public function startTrans()
    {
        $this->db()->beginTransaction();
    }

    public function rollbackTrans()
    {
        $this->db()->rollBack();
    }

    public function commitTrans()
    {
        $this->db()->commit();
    }

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

    public function setAttr($attr, $value)
    {
        $this->db()->setAttribute($attr, $value);
    }

    public function getAttr($attr)
    {
        $this->db()->getAttribute($attr);
    }




}
