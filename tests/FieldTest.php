<?php
/**
 * Created by PhpStorm.
 * User: Pash
 * Date: 13.02.2016
 * Time: 10:45
 */

namespace MiniForm\Tests;

use MiniForm\Form;
use MiniForm\Field;

class FieldTest extends \PHPUnit_Framework_TestCase
{
    private function createField()
    {
        $field = new Field();

        return $field;
    }

    public function testIsValid()
    {
        $field = $this->createField();
        $this->assertTrue($field->isValid());
        $field->addError("test error");
        $this->assertFalse($field->isValid());
    }

    public function testAddError()
    {
        $field = $this->createField();
        $field->addError("test error");
        $this->assertFalse($field->isValid());
    }
    public function testClearErrors()
    {
        $field = $this->createField();
        $field->addError("test error");
        $field->clearErrors();
        $this->assertTrue($field->isValid());
    }
    public function testGetErrors()
    {
        $field = $this->createField();
        $field->addError("test error1");
        $field->addError("test error2");
        $errors = $field->getErrors();
        $this->assertEquals($errors, ["test error1", "test error2"]);
    }

    public function testGetErrorsAsString()
    {
        $field = $this->createField();
        $errors = $field->getErrorsAsString();
        $this->assertEquals($errors, "");
        $field->addError("test error1");
        $field->addError("test error2");
        $errors = $field->getErrorsAsString();
        $this->assertEquals($errors, "test error1, test error2");
        $errors = $field->getErrorsAsString("--");
        $this->assertEquals($errors, "test error1--test error2");
    }

    public function testHasAttribute()
    {
        $testAttr = "test";
        $field = $this->createField();
        $this->assertFalse($field->hasAttribute($testAttr));
        $field->addAttribute($testAttr, "");
        $this->assertTrue($field->hasAttribute($testAttr));
    }

    public function testAddAttribute()
    {
        $testAttrName = "test";
        $field = $this->createField();
        $this->assertFalse($field->hasAttribute($testAttrName));
        $field->addAttribute($testAttrName, "");
        $this->assertTrue($field->hasAttribute($testAttrName));
    }

    public function testSetAttribute()
    {
        $testAttrName = "test";
        $field = $this->createField();
        $this->assertFalse($field->hasAttribute($testAttrName));
        $field->setAttribute($testAttrName, "");
        $this->assertTrue($field->hasAttribute($testAttrName));
    }

    public function testGetAttribute()
    {
        $testAttrName = "test";
        $testAttrValue = "value";
        $field = $this->createField();
        $this->assertNull($field->getAttribute($testAttrName));
        $field->setAttribute($testAttrName, $testAttrValue);
        $this->assertEquals($testAttrValue, $field->getAttribute($testAttrName));
    }
}