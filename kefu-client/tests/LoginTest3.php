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
     * @param $userName
     * @param $password
     * @dataProvider providerForSignIn
     */
    public function testSignIn($userName, $password)
    {
        $login = new Login();
        $this->assertTrue($login->signIn($userName, $password));
    }

    public function providerForSignIn()
    {
        return array(
            '用户名为空' => ['', '000000'],
            '密码为空' => ['000000', ''],
            '密码不正确' => ['3675', '000000'],
            '用户名与密码正确A' => ['1607', '123456'],
            '用户名与密码正确B' => ['3221', '123456'],
            '用户不存在' => ['xxxx', '123456'],
            '状态异常' => ['4632', '123456'],
        );
    }

}





