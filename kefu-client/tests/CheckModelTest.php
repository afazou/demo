<?php
use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Extensions_Database_TestCase_Trait as DatabaseTestCaseTrait;
use PHPUnit_Extensions_Database_DataSet_ArrayDataSet as DatabaseArrayDataSet;
class CheckModelTest extends TestCase
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
            // 表中初始化两条数据
            'wewin8_check' => array(
                array('uid' => 1, 'session_id' => 'joy', 'endtime' => '111111'), // 向表wewin8_check中插入数据
                array('uid' => 2, 'session_id' => 'sam', 'endtime' => '222222'), // 向表wewin8_check中插入数据
            ),
        );
    }

    /**
     * 测试向表中插入数据
     */
    public function testInsert()
    {
        $checkM = new \Kefu\Model\Check();

        // 表中已初始化2条数据，再插入一条，共计3条。
        $checkM->insert(array('uid' => 2, 'session_id' => uniqid(), 'endtime' => time()));
        // 获取表中的行数
        $this->assertEquals(3, $this->getConnection()->getRowCount('wewin8_check'), "插入失败！");

    }
}





