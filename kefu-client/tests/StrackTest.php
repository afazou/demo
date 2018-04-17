<?php
/**
 * 断言(Assertions)是PHPUnit提供的一系列对程序执行结果测试的方法。通俗的讲，就是断言执行程序结果为我们期待的值。
 */
require dirname(__FILE__) . '/../vendor/autoload.php';
class StrackTest extends PHPUnit_Framework_TestCase
{
       // 1. 测试方法之间的依赖++++++++++++++++++++++++++++++++++++++++++++++++
           /*public function testOne()
        {
            $this>assertTrue(true);
            return 'first';
        }

        public function testTwo()
        {
            $this>assertFalse(false);
            return 'second';
        }*/
   
           /**
            * @depends testOne
            * @depends testTwo
            */
           /*public function testX($a, $b)
        {
            echo $a . PHP_EOL;
            echo $b . PHP_EOL;
            $this>assertEquals(['first', 'second'], func_get_args());
        }*/
   
   
   
       // 2. 数据供给器++++++++++++++++++++++++++++++++++++++++++++++++++++
           /*public function addProvider()
        {
            return [
                'adding zeros'  => [0, 0, 0],
                'zero plus one' => [0, 1, 1],
                'one plus zero' => [1, 0, 1],
                'one plus one'  => [1, 1, 3]
            ];
        }*/
   
           /**
            * @dataProvider addProvider
            */
           /*public function testAdd($a, $b, $c)
        {
            $this>assertEquals($c, $a + $b);
        }*/
   
   
       // 3. 测试被测代码中是否抛出了异常++++++++++++++++++++++++++++++++++++
       /*public function addProviderEx()
    {
        return [
            'one' => ['a'],
            'two' => [3],
            'three' => ['2'],
        ];
    }*/
   
       /**
        * 方法一
        *
        * @param $num
        * @dataProvider addProviderEx
        */
       /*public function testAException($num)
    {
        $this>expectException(InvalidArgumentException::class);
        if (!is_int($num)) {
            throw new InvalidArgumentException('非整型');
        }
    }*/
   
       /**
        * 方法二
        *
        * @param $num
        * @dataProvider addProviderEx
        * @expectedException   InvalidArgumentException
        */
       /*public function testBException($num)
    {
        if (!is_int($num)) {
            throw new InvalidArgumentException('非整型');
        }
    }*/
   
       // 4. 对 PHP 错误进行测试++++++++++++++++++++++++++++++++++++
   
       /**
        *
        * 以下测试断言代码中存在PHP错误
        *
        * 有时候我们的代码在运行时会出现php错误,如整除0,文件不存在等等。PHPUnit中,它会自动把错误转换为异常PHPUnit_Framework_Error并抛出,我们只需要在测试方法中设定抓取这个异常即可。
        *
        * @param $a
        * @param $b
        * @dataProvider addProviderExB
        * @expectedException PHPUnit_Framework_Error
        */
       public function testCException($a, $b)
    {
               if ($a / $b > 1) {
           
                   }
    }

    public function addProviderExB()
    {
               return [
                       'one' => [3, 0],
                       'two' => [1, 0],
                       'three' => [4, 0],
                   ];
    }

}




