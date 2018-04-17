<?php
/**
 *      [Gome Wap!] (C)2013-2023 Gome Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: input.php 2013-11-18 14:13:22Z lilixing $
 */
class Input {
	// Enable or disable automatic XSS cleaning
	protected $use_xss_clean = TRUE;

	// Are magic quotes enabled?
	protected $magic_quotes_gpc = FALSE;

	// IP address of current user
	public $ip_address;

	// Request Type
	protected static $request_type = 'GET';

	// Request Key
	protected static $request_key = '';

	// Input singleton
	protected static $instance;

	/**
	 * Retrieve a singleton instance of Input. This will always be the first
	 * created instance of this class.
	 *
	 * @return  object
	 */
	public static function get_instance()
	{
		if (Input::$instance === NULL)
		{
			// Create a new instance
			return new Input;
		}

		return Input::$instance;
	}

	/**
	 * Sanitizes global GET, POST and COOKIE data. Also takes care of
	 * magic_quotes and register_globals, if they have been enabled.
	 *
	 * @return  void
	 */
	public function __construct()
	{
		if (Input::$instance === NULL)
		{
			// magic_quotes_runtime is enabled
			if (get_magic_quotes_runtime())
			{
				set_magic_quotes_runtime(0);
			}

			// magic_quotes_gpc is enabled
			if (get_magic_quotes_gpc())
			{
				$this->magic_quotes_gpc = TRUE;
			}

			// register_globals is enabled
			if (ini_get('register_globals'))
			{
				if (isset($_REQUEST['GLOBALS']))
				{
					// Prevent GLOBALS override attacks
					exit('Global variable overload attack.');
				}

				// Destroy the REQUEST global
				$_REQUEST = array();

				// These globals are standard and should not be removed
				$preserve = array('GLOBALS', '_REQUEST', '_GET', '_POST', '_FILES', '_COOKIE', '_SERVER', '_ENV', '_SESSION');
				$diffs = array_diff(array_keys($GLOBALS), $preserve);
				// This loop has the same effect as disabling register_globals
				foreach ($diffs as $key)
				{
					// Unset the global variable
					unset($GLOBALS[$key]);
				}

				//$this->_sanitize_globals();
			}
			//zhangxl注释，由于post数据或者get过来的数据clean后可能不完整，要过滤请使用 $this->input->get('ctl', '', false)等进行操作
			// if (is_array($_GET))
			// {
			// 	foreach ($_GET as $key => $val)
			// 	{
			// 		// Sanitize $_GET
			// 		$_GET[$this->clean_input_keys($key)] = $this->clean_input_data($val);
			// 	}
			// }
			// else
			// {
			// 	$_GET = array();
			// }

			// if (is_array($_POST))
			// {
			// 	foreach ($_POST as $key => $val)
			// 	{
			// 		// Sanitize $_POST
			// 		$_POST[$this->clean_input_keys($key)] = $this->clean_input_data($val);
			// 	}
			// }
			// else
			// {
			// 	$_POST = array();
			// }

			// if (is_array($_COOKIE))
			// {
			// 	foreach ($_COOKIE as $key => $val)
			// 	{
			// 		// Ignore special attributes in RFC2109 compliant cookies
			// 		if ($key == '$Version' OR $key == '$Path' OR $key == '$Domain')
			// 		{
			// 			continue;
			// 		}

			// 		// Sanitize $_COOKIE
			// 		$_COOKIE[$this->clean_input_keys($key)] = $this->clean_input_data($val);
			// 	}
			// }
			// else
			// {
			// 	$_COOKIE = array();
			// }

			// Create a singleton
			Input::$instance = $this;
		}
	}

	/**
	 * Fetch an item from the $_GET array.
	 *
	 * @param   string   key to find
	 * @param   mixed    default value
	 * @param   boolean  XSS clean the value
	 * @return   mixed
	 */
	public function get($key = array(), $default = NULL, $xss_clean = TRUE)
	{
		self::$request_type = 'GET';
		return $this->search_array($_GET, $key, $default, $xss_clean);
	}

	/**
	 * Fetch an item from the $_POST array.
	 *
	 * @param   string   key to find
	 * @param   mixed    default value
	 * @param   boolean  XSS clean the value
	 * @return   mixed
	 */
	public function post($key = array(), $default = NULL, $xss_clean = TRUE)
	{
		self::$request_type = 'POST';
		return $this->search_array($_POST, $key, $default, $xss_clean);
	}

	/**
	 * Fetch an item from the $_COOKIE array.
	 *
	 * @param   string   key to find
	 * @param   mixed    default value
	 * @param   boolean  XSS clean the value
	 * @return  mixed
	 */
	public function cookie($key = array(), $default = NULL, $xss_clean = TRUE)
	{
		self::$request_type = 'COOKIE';
		return $this->search_array($_COOKIE, $key, $default, $xss_clean);
	}

	/**
	 * Fetch an item from the $_SERVER array.
	 *
	 * @param   string   key to find
	 * @param   mixed    default value
	 * @param   boolean  XSS clean the value
	 * @return  mixed
	 */
	public function server($key = array(), $default = NULL, $xss_clean = TRUE)
	{
		self::$request_type = 'SERVER';
		if (is_string($key) && $key == 'QUERY_STRING')
		{
			self::$request_key = $key;
		}
		return $this->search_array($_SERVER, $key, $default, $xss_clean);
	}

	/**
	 * Fetch an item from a global array.
	 *
	 * @param   array    array to search
	 * @param   string   key to find
	 * @param   mixed    default value
	 * @param   boolean  XSS clean the value
	 * @return  mixed
	 */
	protected function search_array($array, $key, $default = NULL, $xss_clean = FALSE)
	{
		if ($key === array())
		{
			return $array;
		}

		if (is_array($key))
		{
			$values = array();
			foreach ($key as $name)
			{
				if ( ! isset($array[$name]))
				{
					$values[$name] = $default;
					continue;
				}

				// Get the value
				$value = $array[$name];

				if ($this->use_xss_clean === TRUE AND $xss_clean === TRUE)
				{
					// XSS clean the value
					$value = $this->xss_clean($value);
				}
				$values[$name] = $value;
			}
			return $values;
		}

		if ( ! isset($array[$key]))
		{
			return $default;
		}

		// Get the value
		$value = $array[$key];

		if ($this->use_xss_clean === TRUE AND $xss_clean === TRUE)
		{
			// XSS clean the value
			$value = $this->xss_clean($value);
		}

		return $value;
	}

	/**
	 * Fetch the IP Address.
	 *
	 * @return string
	 */
	public function ip_address()
	{
		if ($this->ip_address !== NULL)
		{
			return $this->ip_address;
		}

		// Server keys that could contain the client IP address
		$keys = array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');

		foreach ($keys as $key)
		{
			if ($ip = $this->server($key))
			{
				$this->ip_address = $ip;

				// An IP address has been found
				break;
			}
		}

		if ($comma = strrpos($this->ip_address, ',') !== FALSE)
		{
			$this->ip_address = substr($this->ip_address, strrpos($this->ip_address, ',') + 1);
		}

		if ( ! $this->valid_ip($this->ip_address))
		{
			// Use an empty IP
			$this->ip_address = '0.0.0.0';
		}

		return $this->ip_address;
	}

	/**
	 * Validate IP
	 *
	 * @param   string   IP address
	 * @param   boolean  allow IPv6 addresses
	 * @return  boolean
	 */
	public function valid_ip($ip, $ipv6 = FALSE)
	{
		// Do not allow private and reserved range IPs//要求值是 RFC 指定的私域 IP （比如 192.168.0.1） ||  要求值不在保留的 IP 范围内。该标志接受 IPV4 和 IPV6 值。
		$flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;

		if ($ipv6 === TRUE)
		{
			return (bool) filter_var($ip, FILTER_VALIDATE_IP, $flags);
		}

		//return (bool) filter_var($ip, FILTER_VALIDATE_IP, $flags | FILTER_FLAG_IPV4);  //（规定要过滤的ip变量,规定要使用的过滤器(把值作为 IP 地址来验证),|FILTER_FLAG_IPV4 - 要求值是合法的 IPv4 IP（比如 255.255.255.255） ）
		return (bool) filter_var($ip, FILTER_VALIDATE_IP);
	}

	/**
	 * Clean cross site scripting exploits from string.
	 * HTMLPurifier may be used if installed, otherwise defaults to built in method.
	 * Note - This function should only be used to deal with data upon submission.
	 * It's not something that should be used for general runtime processing
	 * since it requires a fair amount of processing overhead.
	 *
	 * @param   string  data to clean
	 * @param   string  xss_clean method to use ('htmlpurifier' or defaults to built-in method)
	 * @return  string
	 */
	public function xss_clean($data, $tool = NULL)
	{
		if (is_array($data))
		{
			foreach ($data as $key => $val)
			{
				$data[$key] = $this->xss_clean($val, $tool);
			}

			return $data;
		}

		// Do not clean empty strings
		$data = trim($data);
		if ($data === '')
		{
			return $data;
		}

		// Fix &entity\n;
		$data = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $data);
		$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
		$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
		$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

		// Remove any attribute starting with "on" or xmlns
		$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

		// Remove javascript: and vbscript: protocols
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

		// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

		// Remove namespaced elements (we do not need them)
		$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

		do
		{
			// Remove really unwanted tags
			$old_data = $data;
			$data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
		} while ($old_data !== $data);

		//Only get\post remove js xss
		if (json_decode($data) == false && in_array(self::$request_type, array('GET', 'POST')))
		{
			$data = $this->remove_xss($data);
		}
		//$_SERVER['QUERY_STRING']  remove js xss
		if ( ! empty($data) && self::$request_type == 'SERVER' && self::$request_key == 'QUERY_STRING')
		{
			$split_arr = explode('&', $data);
			if (is_array($split_arr))
			{
				foreach ($split_arr as $k => $v)
				{
					$two_split_arr = explode('=', $v);
					$two_split_arr[1] = $this->remove_xss($two_split_arr[1]);
					$merge_arr[] = implode('=', $two_split_arr);
				}
			}
			$data = implode('&', $merge_arr);
		}

		return $data;
	}

	/**
	 * @description 过滤xss攻击
	 *
	 * @param	$val 需要被过滤数据
	 * @return	string
	 *
	 * @version	1.0 2014-9-1 下午02:00:56
	 * @author	<gxn>gaoxinna@yolo24.com
	 *
	 */
	private function remove_xss($val)
	{
		//$val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
		$search = 'abcdefghijklmnopqrstuvwxyz';
		$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$search .= '1234567890!@#$%^&*()';
		$search .= '~`";:?+/={}[]-_|\'\\';
		$len_search = strlen($search);
		for ($i = 0; $i < $len_search; $i++)
		{
			// ;? matches the ;, which is optional
			// 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

			// @ @ search for the hex values
			$val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
			// @ @ 0{0,7} matches '0' zero to seven times
			$val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
		}

		// now the only remaining whitespace attacks are \t, \n, and \r
		$ra1 = Array('javascript', 'vbscript', 'script', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
		$ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload', 'alert', 'prompt', 'confirm');
		$ra  = array_merge($ra1, $ra2);

		$found = true; // keep replacing as long as the previous round replaced something
		while ($found == true)
		{
			$val_before = $val;
			$len_ra     = count($ra);
			for ($i = 0; $i < $len_ra; $i++)
			{
				$pattern    = '/';
				$len_ra_sub = strlen($ra[$i]);
				for ($j = 0; $j < $len_ra_sub; $j++)
				{
					if ($j > 0)
					{
						$pattern .= '(';
						$pattern .= '(&#[xX]0{0,8}([9ab]);)';
						$pattern .= '|';
						$pattern .= '|(&#0{0,8}([9|10|13]);)';
						$pattern .= ')*';
					}
					$pattern .= $ra[$i][$j];
				}
				$pattern .= '/i';

				//$replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
				$replacement = '';
				$val = preg_replace($pattern, $replacement, $val); // filter out the hex tags

				if ($val_before == $val)
				{
					// no replacements were made, so exit the loop
					$found = false;
				}
				else
				{
					return '';
				}
			}
		}

		return $val; //htmlspecialchars($val);
	}

	/**
	 * This is a helper method. It enforces W3C specifications for allowed
	 * key name strings, to prevent malicious exploitation.
	 *
	 * @param   string  string to clean
	 * @return  string
	 */
	public function clean_input_keys($str)
	{
		$chars = PCRE_UNICODE_PROPERTIES ? '\pL' : 'a-zA-Z';

		if ( ! preg_match('#^['.$chars.'0-9:_.-/]++$#uD', $str))
		{
			//exit('Disallowed key characters in global data.');
		}

		return $str;
	}

	/**
	 * This is a helper method. It escapes data and forces all newline
	 * characters to "\n".
	 *
	 * @param   unknown_type  string to clean
	 * @return  string
	 */
	public function clean_input_data($str)
	{
		if (is_array($str))
		{
			$new_array = array();
			foreach ($str as $key => $val)
			{
				// Recursion!
				$new_array[$this->clean_input_keys($key)] = $this->clean_input_data($val);
			}
			return $new_array;
		}

		if ($this->magic_quotes_gpc === TRUE)
		{
			// Remove annoying magic quotes
			$str = stripslashes($str);
		}

		if ($this->use_xss_clean === TRUE)
		{
			$str = $this->xss_clean($str);
		}

		if (strpos($str, "\r") !== FALSE)
		{
			// Standardize newlines
			$str = str_replace(array("\r\n", "\r"), "\n", $str);
		}

		return trim($str);
	}

	/**
	 * Sanitize Globals
	 *
	 * This function does the following:
	 *
	 * Unsets $_GET data (if query strings are not enabled)
	 *
	 * Unsets all globals if register_globals is enabled
	 *
	 * Standardizes newline characters to \n
	 *
	 * @access	private
	 * @return	void
	 */
	private function _sanitize_globals()
	{
		//It would be "wrong" to unset any of these GLOBALS.
		$protected = array('_SERVER', '_GET', '_POST', '_FILES', '_REQUEST', '_SESSION', '_ENV', 'GLOBALS', 'HTTP_RAW_POST_DATA', 'system_folder', 'application_folder', 'BM', 'EXT', 'CFG', 'URI', 'RTR', 'OUT', 'IN');

		// Unset globals for securiy.
		// This is effectively the same as register_globals = off
		foreach (array($_GET, $_POST, $_COOKIE) as $global)
		{
			if ( ! is_array($global))
			{
				if ( ! in_array($global, $protected))
				{
					global $$global;
					$$global = NULL;
				}
			}
			else
			{
				foreach ($global as $key => $val)
				{
					if ( ! in_array($key, $protected))
					{
						global $$key;
						$$key = NULL;
					}
				}
			}
		}
	}
}