<?php
class My_Controller extends sys_controller
{
	protected $tpl;
	public function __construct()
	{
		parent::__construct();

		$this->getTpl();
	}

	//替换模板
	public function display($tmpl, $args = array())
	{
		if(!empty($args))
		{
			$this->tpl->assign($args);
		}
		$this->tpl->display($tmpl);
	}

    public function assign($args, $value)
    {
        $this->tpl->assign($args, $value);
    }

	//获取模版对象
	public function getTpl()
	{
		//默认模版加载方式
		//$this->tpl = new sys_templates(sys_config::Get('templates_dir'), sys_config::Get('comple_dir'));
		//smarty 加载方式
		$this->tpl = new Smarty();
		$this->tpl->template_dir = sys_config::Get('templates_dir');
		$this->tpl->compile_dir = sys_config::Get('comple_dir');
		$this->tpl->left_delimiter = "<!--{";
		$this->tpl->right_delimiter = "}-->";
	}
}





?>
