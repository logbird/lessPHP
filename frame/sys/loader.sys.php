<?php
/**
 * 类的加载
 */
class sys_loader
{
	//model缓存
	private static $_objectCache = array();
	//已注册的插件
	private static $_plugin = array();

	function __construct()
	{
	}

	/**
	 * 加载model层
	 * @param classPath string model路径 和model名称 例如 加载 user/userModel.php 为 loadModel("user/user");
	 * @param args array 给model构造函数 传递的参数
	 * @param isCache boolean 是否 缓存model对象 默认为 缓存
	 **/
	public static function loadModel($classPath, $args = array(), $isCache = true)
	{
		$classPath = trim($classPath, '/');
		//判断缓存里是否已经存在这个对象 否则创建新的对象
		if(isset(self::$_objectCache[$classPath]) && is_object(self::$_objectCache[$classPath]))
		{
			return self::$_objectCache[$classPath];
		}
		//拆分路径
		$path = explode('/', $classPath);
		//获得类名
		$className = array_pop($path).'Model';
		//获取model路径
		$path = implode('/', $path);
		$path = empty($path)? DIRECTORY_SEPARATOR : DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR;
		$path = sys_config::Get('model_dir').$path.$className.'.php';
		//加载model
        try
        {
            if(is_file($path))
            {
                require_once($path);
                return new $className($args);
            }else
            {
                throw new sys_exception($path . ' Not Exists!');
            }
        }
        catch(sys_exception $ce)
        {
            $ce->showMsg();
        }
	}
	
	/**
	 * 注册用户插件
	 * @param className string 插件的类名
	 * @param path string 插件在 plugin 内的 路径
	 * @param lazyLoad boolean false为立即加载 true为使用时自动加载
	 **/
	public static function register($className, $path, $lazyLoad = true)
	{
		$pluginDir = sys_config::Get('plugin_dir');
		$path = $pluginDir . '/' . trim($path, '/');

        try
        {
            //检查插件是否存在
            if(!file_exists($path))
            {
                throw new sys_exception('Plugin ' . $className . ' is not found!');
            }
        }catch(sys_exception $ce)
        {
            $ce->showMsg();
        }

		//立即加载
		if(!$lazyLoad)
		{
			require_once($path);
		}
		self::$_plugin[$className] = array(
			'path' => $path,
			'lazyLoad' => $lazyLoad,
		);
	}

	/**
	 * 加载用户插件
	 * @param className string 插件的类名
	 * @param args array 插件构造函数的参数
	 */
	public static function loadPlugin($className, $args = array())
	{
        try
        {
            if(!isset(self::$_plugin[$className]))
            {
                throw new sys_exception($className . ' Not Registered!');
            }
        }catch(sys_exception $ce)
        {
            $ce->showMsg();
        }
		$plugin = self::$_plugin[$className];
		//是否已经加载 true 为 未加载
		if($plugin['lazyLoad'])
		{
			require_once($plugin['path']);
		}
		$obj = null;
		if(empty($args))
		{
			$obj = new $className();
		}else
		{
			$obj = new $className($args);
		}
		return $obj;
	}

	/**
	 * 如果存在插件 则 获得插件 路径
	 * @param className string 插件的类名
	 */
	public static function getPlugin($className)
	{
        if(!isset(self::$_plugin[$className]))
        {
            return False;
        }
		$plugin = self::$_plugin[$className];
		return $plugin['path'];
	}


}
