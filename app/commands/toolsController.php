<?php
/**
 * 工具脚本类
 * 
 * @uses My
 * @uses _Controller
 * @package 
 * @version $id$
 * @copyright @copyright 2005-2012 360.CN All Rights Reserved.
 * @author logbrid <logbird@126.com> 
 * @license 
 */
class toolsController extends My_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function testAction($count = 5, $delay = 1)
	{
        $count = intval($count);
        $delay = intval($delay);
        for ($i = 0; $i < $count; $i++)
        {
            echo "This is Cli Progream!\n";
            flush();
            sleep($delay);
        }
	}
}

