<?php

class sys_exception extends Exception {
  
	private $backtrace;
  
	public function __construct($message, $code = 0)
    {
		parent::__construct($message, $code);
	}

    public function showMsg()
    {

        $msg = $this->getMsg();
        echo $msg;
    }

    public function getMsg()
    {
        $stace = $this->getTrace();
        $msg = "<style>";
        $msg .= ".ce { width:950px; background:#ffeeee; margin-bottom:30px;}";
        $msg .= ".ce div { width:940px;border:1px solid #bbb; margin-top:10px; padding:5px;}";
        $msg .= ".ce strong { font-size:14px; font-weight: bold;}";
        $msg .= ".ce p{ font-size:12px;}";
        $msg .= "</style>";
        $msg .= "<div class = 'ce'>";
        $msg .= "<div style = 'background:#FFFF6F;'>";
        $msg .= "<strong style = 'color:#ff0000;font-size:22px;font-weight:bold;'>{$this->getMessage()}</strong><br >";
        $msg .= "<strong>{$this->getFile()}:{$this->getLine()} </strong>";
        $msg .= "</div>";
        foreach($stace as $v)
        {
            $ext = '';
            if(isset($v['function']) && isset($v['class']))
            {
                $ext = $v['class'] . '-&gt;' . $v['function'];
            }elseif(isset($v['class']))
            {
                $ext = ' CLASS ' . $v['class'];
            }elseif(isset($v['function']))
            {
                $ext = ' FUNCTION ' . $v['function'];
            }
            if($ext)
            {
                $ext = "<span style = 'color:#ff0000;'>(".$ext.")</span>";
            }
            if(isset($v['file']))
            {
                $msg .= $this->getErrSource($v['file'], $v['line'], $ext);
            }
        }
        $msg .= "</div>";
        return $msg;
    }

    private function getErrSource($file, $line, $ext)
    {
        if(!$file)
        {
            return '';
        }
        $errSource = "<div>";
        $errSource .= "<strong>{$file}:{$line} $ext </strong>";
        $source = explode("\n", trim(file_get_contents($file)));
        $line--;
        for($i = $line - 2; $i <= $line + 2; $i++)
        {
            if(!isset($source[$i]))continue;
            $code = $source[$i];
            $code = str_replace("&nbsp;", '', $code);
            $code = htmlspecialchars($code);
            $code = str_replace("\t", str_repeat("&nbsp;", 4), $code);
            $code = str_replace(" ", "&nbsp;", $code);
            if($i == $line)
            {
                $errSource .= "<p style = 'color:#ff0000;'>{$i}:&nbsp;&nbsp;{$code}</p>";
            }else
            {
                $errSource .= "<p>{$i}:&nbsp;&nbsp;{$code}</p>";
            }
        }
        $errSource .= "</div>";
        return $errSource;
    }
}
