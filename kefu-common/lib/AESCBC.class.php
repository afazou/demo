<?php
/**
 * AES 加密解密函数 CBC模式，与js互通
 * 用法：
 * $key = "0000000000000000";
 * $iv = "@12345678912345!"(128位模式偏移量需要16位字符)
 * $data = "gome1234";
 * $crypt = new DES($key);
 * $value = $crypt->encrypt($data);
 * echo $value.'<br/>';
 * echo $crypt->decrypt($value);
 * @author	<arvin>xuqingwei@gomeplus.com 
 * @createtime 2017-06-05
 */
class AESCBC{

	private $key;
	private $iv;

	public function __construct($key,$iv = ''){
		$this->key = $key;
        $this->iv = empty($iv) ? $key : $iv;
	}

	/**
	 * 加密
	 * @author xuqingwei@gomeplus.com
	 * @param  [type] $key [description]
	 * @return [type]      [description]
	 */

	public function encrypt($input) {
        $data = mcrypt_encrypt(MCRYPT_RIJNDAEL_128,$this->key,$input,MCRYPT_MODE_CBC,$this->iv);
		$data = base64_encode($data);
		return $data;
	}

    public function decrypt($input) {
        $encryptedData=base64_decode($input);
        $data = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,$this->key,$encryptedData,MCRYPT_MODE_CBC,$this->iv);
        $data = rtrim($data,"\0");//解密出来的数据后面会出现如图所示的六个红点；这句代码可以处理掉，从而不影响进一步的数据操作
		return $data;
    }
}

 
 

