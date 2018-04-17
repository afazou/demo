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



    /**
     * 模拟登录账号
     *
     * @return array
     */
    public function providerForAccount()
    {
        return array(
            '用户不存在' => ['xxxx', '123456'],          // 源代码执行时抛出异常且断言成功，后面的相等断言不再执行---------》所以只进行了1次断言
            '密码不正确' => ['3675', '000000'],          // 源代码执行时抛出异常且断言成功，后面的相等断言不再执行---------》所以只进行了1次断言
            '用户名与密码正确' => ['1607', '123456'],     // 源代码执行时没有抛出异常所以执行了assertEquals断言，如果assertEquals断言失败，不再进行下一个断言，如果assertEquals断言成功，则会继续下一个断言（所以它有可能执行@expectedException断言，也有可能不执行）---------》所以进行了1次或多次断言
        );
    }

    /**
     * 测试获取登录账号信息
     *
     * 注：对一个测试方法使用了多个断言，如该方法同时使用了异常断言(@expectedException)与相等断言(assertEquals)，那断言执行顺序是如何的呢？实际上是按源代码的顺序来的。
     *
     * 另外要注意的是如果assertEquals断言失败，那么后面的断言不会再执行。
     * 如：
        $stack = [];
        $this->assertEquals(1, count($stack));  // 会执行
        $this->assertEquals(0, count($stack));  // 不会执行
        所以最终只执行了1个断言，失败了1个断言。通过查看源代码发现，如果断言失败它会抛出一个异常，所以后面的不再执行。
     *

     *
     *
     * @param $userName
     * @param $password
     *
     * @covers \Kefu\Lib\Login::getAccount()
     * @dataProvider providerForAccount
     * @expectedException \Kefu\Lib\Exception
     */
    /*public function testGetAccount($userName, $password)
    {
        $login = new Login();
        $account = $login->getAccount($userName, $password);    // 此行如果抛出了异常，下面的代码不会再执行。
        $this->assertEquals($this->getExpectedAccountQuery(), $account);
    }*/

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

    public function testX()
    {
        $this->expectOutputString('foo');
        print 'f';
        $this->assertEquals(0, 0);

    }

}





