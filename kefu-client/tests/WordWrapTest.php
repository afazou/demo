<?php
class WordWrapTest extends PHPUnit_Framework_TestCase
{
    function testItCanWrap() {
        $w = new Kefu\Lib\WordWrap();

        /*$this->assertEquals('', $w->wrap(null, 0));
        $this->assertEquals('', $w->wrap('', 0));
        $this->assertEquals('a', $w->wrap('a', 1));*/
        $this->assertEquals("anb", $w->wrap('a b', 1));
        $this->assertEquals("a bnc", $w->wrap('a b c', 3));
        $this->assertEquals("anbcnd", $w->wrap('a bc d', 3));
    }

    /**
     *
     * @test
     */
    public function session()
    {
        print_r($_SESSION);
    }

}





