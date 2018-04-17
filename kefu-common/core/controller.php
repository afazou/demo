<?php defined('ROOT_PATH') || die('Access denied!');
/**
 *      [Gome Wap!] (C)2013-2023 Gome Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: controller.php 2013-11-18 14:18:22Z lilixing $
 */
abstract class Controller
{
	protected $id;
	protected $input;
    protected $weixin_error;
	protected static $model = array();

	public function __construct($id = false)
	{
		header("Content-type:text/html;charset=utf-8");
		$this->id = $id;
		$this->input = Input::get_instance();
	}

	protected function assign($k, $v) 
	{
		$this->vars[$k] = $v;
	}

	protected function display($file,$exit = true)
	{
		if(!empty($this->vars) && is_array($this->vars))
		{
			extract($this->vars, EXTR_SKIP);
		}
		include(template($file));
		$exit && exit();
	}
	
	protected function fetch($file,$exit = true)
	{
		if(!empty($this->vars) && is_array($this->vars))
		{
			extract($this->vars, EXTR_SKIP);
		}
		ob_start();
		ob_implicit_flush(0);
		include(template($file));
		$content = ob_get_clean();
		return $content;
	}

	abstract function init();

	protected static function loadModel($model)
	{
		$key =  $model;

		if(array_key_exists($key,self::$model)) 
		{
			return self::$model[$key]->reset();
		}
		else
		{
			self::loader('m',$model);
			$words = explode('/',$model);
			$model = end($words);
			$action = 'mdl_'.$model;

			self::$model[$key] = new $action();
		}

		return self::$model[$key];
	}

	protected static function loader ($type = 'm' ,$name)
	{
		$dir = ROOT_PATH . '/'. $type ;
		if($type == 'm')
		{
			$file=$dir.'/mdl.'.$name.'.php';
			if(!file_exists($file)) 
			{
				$dir=INCLUDE_PATH . '/../m';
			}

			$words = explode('/',$name);
			if(count($words) == 1)
			{
				$file_path = sprintf($dir.'/%s.php','mdl.'.$name);
			}
			else
			{
				$file_path = sprintf($dir.'/%s/%s.php',$words[0],'mdl.'.$words[1]);
			}
		}
		elseif($type == 'lib')
		{
			$dir = INCLUDE_PATH . '/../lib';
			$file_path = sprintf($dir.'/%s.class.php',$name);
		}
		else
		{	
			$file_path = sprintf($dir.'/%s.php',$name);
		}

		if(file_exists($file_path)) 
		{
			include ($file_path);
		}
		else 
		{
			//throw new BaseException(sprintf('The %s is not exists!',$file_path));
		}
	}
}