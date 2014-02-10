<?php
/**
 * 异常处理类
 * 
 * @uses Exception
 * @package 
 * @version $id$
 * @copyright @copyright 2005-2012 360.CN All Rights Reserved.
 * @author logbird <logbird@126.com> 
 * @license 
 */
class sys_exception extends Exception {
  
    private $_template = array();
  
	public function __construct($message, $code = 0)
    {
		parent::__construct($message, $code);
        $this->setTemplate();
	}

    /**
     * 根据 sapi 模式使用不同的模板
     *
     * @access public
     * @return void
     */
    public function setTemplate()
    {
        $sapi = php_sapi_name();
        $tpl = array(); 
        switch($sapi) { 
            case 'cli': 
                $tpl['header'] = "";
                $tpl['title'] = "\033[31m%s:%s\033[0m\n";
                $tpl['staceFlag'] = "->";
                $tpl['stace'] = "\033[32m(%s) \033[0m\n";
                $tpl['source']['top'] = "%s:%s%s";
                $tpl['source']['line'] = "%s:  %s\n";
                $tpl['source']['curline'] = "\033[32m%s:  %s \033[0m\n";
                $tpl['source']['bottom'] = "=====================================================================================\n";
                $tpl['footer'] = "\n";
                break; 
            default:
                $tpl['header'] = "<style>";
                $tpl['header'] .= ".ce { width:950px; background:#ffeeee; margin-bottom:30px;}";
                $tpl['header'] .= ".ce div { width:940px;border:1px solid #bbb; margin-top:10px; padding:5px;}";
                $tpl['header'] .= ".ce strong { font-size:14px; font-weight: bold;}";
                $tpl['header'] .= ".ce p{ font-size:12px;}";
                $tpl['header'] .= "</style>";
                $tpl['title'] = "<div class = 'ce'>";
                $tpl['title'] .= "<div style = 'background:#FFFF6F;'>";
                $tpl['title'] .= "<strong style = 'color:#ff0000;font-size:22px;font-weight:bold;'>%s</strong><br >";
                $tpl['title'] .= "<strong>%s:%s</strong>";
                $tpl['title'] .= "</div>";
                $tpl['staceFlag'] = "-&gt;";
                $tpl['stace'] = "<span style = 'color:#ff0000;'>(%s)</span>";
                $tpl['source']['top'] = "<div><strong>%s:%s%s </strong>";
                $tpl['source']['line'] = "<p>%s:&nbsp;&nbsp;%s</p>";
                $tpl['source']['curline'] = "<p style = 'color:#ff0000;'>%s:&nbsp;&nbsp;%s</p>";
                $tpl['source']['bottom'] = "</div>";
                $tpl['footer'] = '</div>';
            break;
        }
        $this->_template = $tpl;
    }

    /**
     * 源代码格式化
     *
     * @param mixed $code
     * @access public
     * @return void
     */
    public function codeFormat($code)
    {
        $sapi = php_sapi_name();
        $tpl = array(); 
        switch($sapi) 
        { 
            case 'cli': 
                break; 
            default:
                $code = str_replace("&nbsp;", '', $code);
                $code = htmlspecialchars($code);
                $code = str_replace("\t", str_repeat("&nbsp;", 4), $code);
                $code = str_replace(" ", "&nbsp;", $code);
                break;
        }
        return $code;
    }

    /**
     * 输出错误信息并 终止程序
     *
     * @access public
     * @return void
     */
    public function showMsg()
    {

        $msg = $this->getMsg();
        echo $msg;
        exit;
    }

    /**
     * 获取错误提示信息
     *
     * @access public
     * @return void
     */
    public function getMsg()
    {
        $stace = $this->getTrace();
        $msg = '';
        $msg .= $this->_template['header'];
        $msg .= sprintf($this->_template['title'], $this->getMessage(), $this->getFile(), $this->getLine());
        foreach($stace as $v)
        {
            $ext = '';
            if(isset($v['function']) && isset($v['class']))
            {
                $ext = $v['class'] . $this->_template['staceFlag'] . $v['function'];
            }elseif(isset($v['class']))
            {
                $ext = ' CLASS ' . $v['class'];
            }elseif(isset($v['function']))
            {
                $ext = ' FUNCTION ' . $v['function'];
            }
            if($ext)
            {
                $ext = sprintf($this->_template['stace'], $ext);
            }
            if(isset($v['file']))
            {
                $msg .= $this->getErrSource($v['file'], $v['line'], $ext);
            }
        }
        $msg .= $this->_template['footer'];;
        return $msg;
    }


    /**
     * 获取抛出异常的源代码
     *
     * @param mixed $file
     * @param mixed $line
     * @param mixed $ext
     * @access private
     * @return void
     */
    private function getErrSource($file, $line, $ext)
    {
        if(!$file)
        {
            return '';
        }
        $errSource = sprintf($this->_template['source']['top'], $file, $line, $ext);
        $source = explode("\n", trim(file_get_contents($file)));
        $line--;
        for($i = $line - 2; $i <= $line + 2; $i++)
        {
            if(!isset($source[$i]))continue;
            $code = $source[$i];
            $code = $this->codeFormat($code);

            if($i == $line)
            {
                $errSource .= sprintf($this->_template['source']['curline'], $i, $code);
            }else
            {
                $errSource .= sprintf($this->_template['source']['line'], $i, $code);
            }
        }
        $errSource .= $this->_template['source']['bottom'];
        return $errSource;
    }
}
