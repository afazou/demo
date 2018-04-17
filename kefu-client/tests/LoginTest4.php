<?php
use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Extensions_Database_TestCase_Trait as DatabaseTestCaseTrait;
use PHPUnit_Extensions_Database_DataSet_ArrayDataSet as DatabaseArrayDataSet;
use \Kefu\Lib\Login;
use \Kefu\Lib\Exception as KfException;
class LoginTest extends TestCase
{
    use DatabaseTestCaseTrait;

    /**
     * 连接数据库
     *
     * @return PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
     */
    public function getConnection()
    {
        $pdo = new PDO($GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD']);
        return $this->createDefaultDBConnection($pdo, $GLOBALS['DB_DBNAME']);
    }

    /**
     * 初始化数据库表中的数据
     *
     * @return PHPUnit_Extensions_Database_DataSet_ArrayDataSet
     */
    public function getDataSet()
    {
        return new DatabaseArrayDataSet($this->getInitDataSetFromArray());
    }

    protected function getInitDataSetFromArray()
    {
        return array(
            'wewin8_check' => array(
                array('uid' => '179', 'session_id' => 'oru4q2g10fu3m9cvn26tgi72i5', 'endtime' => time()),
            ),
        );
    }

    /**
     * @param $userName
     * @param $password
     * @param $status
     * @dataProvider providerForSignIn
     */
    public function testSignIn($userName, $password, $status)
    {
        $login = new Login();
        new \Kefu\Lib\AddStateLog($login);              // 功能：登录验证通过后记日志
        new \Kefu\Lib\AddLogonIdentifier($login);       // 功能：登录验证通过后添加登录标识
        new \Kefu\Lib\UpdateCustomerStatus($login);     // 功能：登录验证通过后更新登录状态
        $this->assertTrue($login->signIn($userName, $password, $status));
    }

    public function providerForSignIn()
    {
        return array(
            '用户名为空' => ['', '000000', null],
            '密码为空' => ['000000', '', null],
            '密码不正确' => ['3675', '000000', null],
            '用户名与密码正确A' => ['1607', '123456', 3],
            '用户名与密码正确B' => ['3221', '123456', null],
            '用户不存在' => ['xxxx', '123456', null],
            '状态异常' => ['4632', '123456', null],
        );
    }

    public function testSomething()
    {
        $this->markTestIncomplete(
            '此测试目前尚未实现。'
        );
    }


    /**
     * 该方法中的程序代码存在严重bug
     *
     * 通过跑单例测试就能暴露出来
     *
     * @test
     */
    public function encrypt()
    {
        $str = microtime(true);
        $td=mcrypt_module_open(MCRYPT_DES, '', MCRYPT_MODE_CBC, '');
        mcrypt_generic_init($td , 't5o080ktxpwcwt05rduygkrmta2gvrmpvbz15235871761' , pack("H16" , '0102030405060708'));
        $str = base64_encode(mcrypt_generic($td , $this->pkcs5_pad($str , 8)));
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
    }

    private function pkcs5_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

}





