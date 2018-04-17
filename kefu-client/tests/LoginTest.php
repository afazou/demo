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

        );
    }

    public function testNewLogin()
    {
        $login = new Login();
    }

    /**
     * 校验登录参数
     *
     * 用于测试登录参数是否合法
     *
     * @param $userName
     * @param $password
     *
     * @covers \Kefu\Lib\Login::checkLogonParams()
     * @dataProvider providerForLogonParams
     */
    public function testCheckLogonParams($userName, $password)
    {
        $login = new Login();
        $this->expectException(KfException::class); // 断言参数存在问题且会抛出异常KfException
        $login->checkLogonParams($userName, $password);
    }

    /**
     * 模拟登录参数
     *
     * @return array
     */
    public function providerForLogonParams()
    {
        return array(
            '用户名与密码为空' => ['', ''],
            '用户名为空' => ['', '2'],
            '密码为空' => ['1', ''],
        );
    }

    /**
     * 模拟登录用户名
     *
     * @return array
     */
    public function providerForUserName()
    {
        return array(
            ['1607']
        );
    }

    /**
     * 根据用户名获取账号信息
     *
     * 用于测试新接入的数据库抽象层（Medoo）get方法返回的数据结构与原有返回的结构是否一致
     *
     * @param $userName
     *
     * @covers \Medoo\Medoo::get()
     * @dataProvider providerForUserName
     */
    public function testGetAccountInfoByUserName($userName)
    {
        $login = new Login();
        $query = $login->getAccountInfoByUserName($userName);   // 从数据库表中查询一条数据
        $this->assertEquals($this->getExpectedAccountQuery(), $query);
    }

    /**
     * 模拟登录账号
     *
     * @return array
     */
    public function providerForAccount()
    {
        return array(
            '密码不正确' => ['3675', '000000'],
            '用户名与密码正确A' => ['1607', '123456'],
            '用户名与密码正确B' => ['1028', '123456'],
            '用户不存在' => ['xxxx', '123456'],
        );
    }

    /**
     * 测试获取登录账号信息
     *
     * @param $userName
     * @param $password
     * @covers \Kefu\Lib\Login::getAccount()
     * @dataProvider providerForAccount
     * @expectedException \Kefu\Lib\Exception
     */
    public function testGetAccount($userName, $password)
    {
        $method = new \ReflectionMethod('\Kefu\Lib\Login', 'getAccount');
        $method->setAccessible(true);
        $account = $method->invoke(new Login(), $userName, $password);
        $this->assertEquals($this->getExpectedAccountQuery(), $account);
        return $account;
    }

    protected function getExpectedAccountQuery()
    {
        return array(
            'id' => 184,
            'token' => '',
            'username' => '1607',
            'password' => '123456',
            'name' => '韩雪娇',
            'only' => '564184ecb9e90',
            'sex' => '0',
            'entry_time' => '1447084800',
            'quit_time' => '0',
            'is_serving' => '1',
            'key' => '1',
            'reception_num' => '2',
            'team' => null,
            'status' => '1',
        );
    }

    /**
     * @param $account
     * @dataProvider providerForCheckAccountState
     * @expectedException \Kefu\Lib\Exception
     */
    public function testCheckAccountState($userName)
    {
        $login = new Login();
        $account = $login->getAccountInfoByUserName($userName);
        $method = new \ReflectionMethod('\Kefu\Lib\Login', 'checkAccountState');
        $method->setAccessible(true);
        $this->assertTrue($method->invoke(new Login(), $account));
    }

    public function providerForCheckAccountState()
    {
        return array(
//            'x' => ['3221'],
            'y' => ['3071'],
        );
    }

    /**
     * @param $userName
     * @param $password
     * @dataProvider providerForSignIn
     * @covers \Kefu\Lib\Login::signIn()
     */
    public function testSignIn($userName, $password)
    {
        $login = new Login();
        $this->assertTrue($login->signIn($userName, $password));
    }

    public function providerForSignIn()
    {
        return array(
            '密码不正确' => ['3675', '000000'],
            '用户名与密码正确A' => ['1607', '123456'],
            '用户名与密码正确B' => ['3221', '123456'],
            '用户不存在' => ['xxxx', '123456'],
        );
    }

}





