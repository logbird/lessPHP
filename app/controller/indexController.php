<?php
class indexController extends My_Controller
{
	public function __construct()
	{
		parent::__construct();
        $this->msg = $this->loadModel('message');
	}

	public function indexAction()
	{
        $list = $this->msg->getList(1);
        $this->assign('list', $list);
        $this->display('index.html');
	}

    public function addAction()
    {
        $nickname = $this->post('nickname');
        $content = $this->post('content');
        $id = $this->msg->add($nickname, $content);
        if($id > 0)
        {
            toolsLIB::Msg('留言成功！', '/');
        }else
        {
            toolsLIB::Msg('留言失败！', '/');
        }
    }
}

