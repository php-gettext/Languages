<?php

namespace Gettext\Languages\Test;

use Gettext\Languages\Category;

class CategoryTest extends TestCase
{
    public function testConstructorWithInvalidClderId()
    {
        $this->isGoingToThrowException('\Exception');
        new Category('invalid-cldr-category', 'i = 1 and v = 0 @integer 1');
    }

    public function testConstructorOnCldrIdIsNotInList()
    {
        $this->isGoingToThrowException('\Exception');
        new Category('pluralRule-count-10000000', 'i = 1 and v = 0 @integer 1');
    }

    public function testConstructorWithInvalidCldrRule()
    {
        $this->isGoingToThrowException('\Exception');
        new Category('pluralRule-count-one', 'invalid category rule');
    }

    public function testGetExampleIntegers()
    {
        $category = new Category('pluralRule-count-one', 'i = 1 and v = 0 @integer 1');
        $this->assertSame(array(1), $category->getExampleIntegers());
    }
}
