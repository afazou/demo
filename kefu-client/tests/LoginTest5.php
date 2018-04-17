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
        new \Kefu\Lib\AddStateLog($login);                          // 功能：登录验证通过后记日志
        new \Kefu\Lib\AddLogonIdentifier($login);                   // 功能：登录验证通过后添加登录标识
        new \Kefu\Lib\UpdateCustomerStatus($login);                 // 功能：登录验证通过后更新登录状态
        $loginAuth = new \Kefu\Lib\LoginSession($login);             // 功能：登录验证通过后设置登录SESSION
        $this->assertTrue($login->signIn($userName, $password, $status));
        $this->assertNotEmpty($loginAuth->getAuth());
        $this->assertEquals($loginAuth->getAuth(), $login->getSession());
    }

    public function providerForSignIn()
    {
        return array(
            /*'用户名为空' => ['', '000000', null],
            '密码为空' => ['000000', '', null],
            '密码不正确' => ['3675', '000000', null],*/
            '用户名与密码正确A' => ['1607', '123456', 3],
            /*'用户名与密码正确B' => ['3221', '123456', null],
            '用户不存在' => ['xxxx', '123456', null],
            '状态异常' => ['4632', '123456', null],*/
        );
    }

}





