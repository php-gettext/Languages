<?php
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    public function testConstructorWithInvalidClderId()
    {
        $this->setExpectedException('\Exception');
        new \Gettext\Languages\Category('invalid-cldr-category', 'i = 1 and v = 0 @integer 1');
    }

    public function testConstructorOnCldrIdIsNotInList()
    {
        $this->setExpectedException('\Exception');
        new \Gettext\Languages\Category('pluralRule-count-10000000', 'i = 1 and v = 0 @integer 1');
    }

    public function testConstructorWithInvalidCldrRule()
    {
        $this->setExpectedException('\Exception');
        new \Gettext\Languages\Category('pluralRule-count-one', 'invalid category rule');
    }

    public function testGetExampleIntegers()
    {
        $category = new \Gettext\Languages\Category('pluralRule-count-one', 'i = 1 and v = 0 @integer 1');
        $this->assertSame(array(1), $category->getExampleIntegers());
    }
}