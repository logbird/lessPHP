<?PHP

class messageModel extends sys_model
{

    private $_table = 'message';

    public function __construct()
	{
		parent::__construct();
	}

    /**
     * 分页获取留言列表
     *
     * @param mixed $page 页数
     * @param mixed $count 数量
     * @access public
     * @return array
     */
    public function getList($page, $count = 10)
    {
        $page = $page > 0 ? $page - 1 : 0;
        $sql = "SELECT * FROM ".$this->_table . " LIMIT {$page}, {$count}";
        return $this->db()->getAll($sql);
    }

    /**
     * 增加留言
     * 
     * @param mixed $nickname 昵称
     * @param mixed $content 内容
     * @access public
     * @return int
     */
    public function add($nickname, $content)
    {
        $args = array();
        $args['nickname'] = $nickname;
        $args['content'] = $content;
        $args['createtime'] = time();
        $args['ip'] = httpLIB::getIp();
        $args['status'] = 1;
        return $this->db()->add($args, $this->_table);
    }












}
