<?php

namespace Tests\Library\Validation;

use Library\Validation\Validator;
use Tests\BaseTest;

class ValidatorTest extends BaseTest
{
    /**
     * @var Validator
     */
    protected $validator;

    public function setUp()
    {
        parent::setUp();

        $this->validator = new Validator();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testMakeWorksWithSimpleSingleValidationWhenValid()
    {
        // Act
        $result = $this->validator->validate(['one' => 1], ['one' => 'required']);

        // Assert
        $this->assertTrue($result->isValid());
        $this->assertTrue(sizeof($result->errors()) == 0);
    }

    public function testMakeWorksWithSimpleSingleValidationWhenInvalid()
    {
        // Act
        $result = $this->validator->validate(['one' => null], ['one' => 'required']);

        // Assert
        $this->assertFalse($result->isValid());
        $this->assertTrue(sizeof($result->errors()) > 0);
    }

    public function testMakeWorksWithMultipleValidationsWhenAllValid()
    {
        // Act
        $result = $this->validator->validate(
            ['one' => 1, 'two' => 2, 'three' => 3],
            ['one' => 'required', 'two' => 'numeric', 'three' => 'required']
        );

        // Assert
        $this->assertTrue($result->isValid());
        $this->assertTrue(sizeof($result->errors()) == 0);
    }

    public function testMakeWorksWithMultipleValidationsWhenOneInvalid()
    {
        // Act
        $result = $this->validator->validate(
            ['one' => 1, 'two' => 'text', 'three' => 3],
            ['one' => 'required', 'two' => 'numeric', 'three' => 'required']
        );

        // Assert
        $this->assertFalse($result->isValid());
        $this->assertTrue(sizeof($result->errors()) > 0);
    }

    public function testMakeWorksWithMoreDataThanThereAreValidations()
    {
        // Act
        $result = $this->validator->validate(
            ['one' => 1, 'two' => 2, 'three' => 3],
            ['one' => 'required', 'three' => 'required']
        );

        // Assert
        $this->assertTrue($result->isValid());
        $this->assertTrue(sizeof($result->errors()) == 0);
    }

    public function testMakeWorksWithComplexValidationRuleWhenValid()
    {
        // Act
        $result = $this->validator->validate(['one' => 20], ['one' => 'min:15']);

        // Assert
        $this->assertTrue($result->isValid());
        $this->assertTrue(sizeof($result->errors()) == 0);
    }

    public function testMakeWorksWithComplexValidationRuleWhenInvalid()
    {
        // Act
        $result = $this->validator->validate(['one' => 1], ['one' => 'min:15']);

        // Assert
        $this->assertFalse($result->isValid());
        $this->assertTrue(sizeof($result->errors()) > 0);
    }

    public function testMakeWorksWithMultipleValidationsOnSameFieldWhenAllValid()
    {
        // Act
        $result = $this->validator->validate(
            ['one' => 1],
            ['one' => ['required', 'numeric']]
        );

        // Assert
        $this->assertTrue($result->isValid());
        $this->assertTrue(sizeof($result->errors()) == 0);
    }

    public function testMakeWorksWithMultipleValidationsOnSameFieldWhenOneIsInValid()
    {
        // Act
        $result = $this->validator->validate(
            ['one' => 'text'],
            ['one' => ['required', 'numeric']]
        );

        // Assert
        $this->assertFalse($result->isValid());
        $this->assertTrue(sizeof($result->errors()) > 0);
    }

    // ...
    public function testSingleCustomErrorWorks()
    {
        // Act
        $result = $this->validator->validate(
            [],
            ['one' => ['required' => 'custom']]
        );

        // Assert
        $this->assertFalse($result->isValid());
        $this->assertTrue(sizeof($result->errors()) > 0);
        $errors = $result->errors();
        $this->assertEquals('custom', $errors['one'][0]);
    }

    public function testMultipleCustomErrorsWork()
    {
        // Act
        $result = $this->validator->validate(
            ['one' => 'text', 'two' => null],
            [
                'one' => ['required', 'numeric', 'min:10' => 'customMin'],
                'two' => ['required' => 'customRequired', 'min:10']
            ]
        );

        // Assert
        $this->assertFalse($result->isValid());
        $this->assertTrue(sizeof($result->errors()) > 0);
        $errors = $result->errors();
        $this->assertEquals(2, sizeof($errors['one']));
        $this->assertEquals('customMin', $errors['one'][1]);
        $this->assertEquals(2, sizeof($errors['two']));
        $this->assertEquals('customRequired', $errors['two'][0]);
    }

    public function testCustomErrorFormatting()
    {
        // Act
        $result = $this->validator->validate(
            ['one' => null],
            ['one' => [
                'required' => '{field} is required',
                'min:10' => '{field} should be higher than {0}'
            ]]
        );

        $this->assertFalse($result->isValid());
        $this->assertTrue(sizeof($result->errors()) > 0);
        $errors = $result->errors();
        $this->assertEquals('one is required', $errors['one'][0]);
        $this->assertEquals('one should be higher than 10', $errors['one'][1]);
    }

    /* SINGLE METHODS */

    public function testThatRequiredWorksWhenValid()
    {
        // Assert
        $this->assertTrue($this->validator->required('test'));
    }

    public function testThatRequiredWorksWhenInvalid()
    {
        // Assert
        $this->assertFalse($this->validator->required(null));
        $this->assertFalse($this->validator->required(''));
        $this->assertFalse($this->validator->required('    '));
    }

    public function testThatNumericWorksWhenValid()
    {
        // Assert
        $this->assertTrue($this->validator->numeric(3));
    }

    public function testThatNumericWorksWhenInvalid()
    {
        // Assert
        $this->assertFalse($this->validator->numeric(null));
        $this->assertFalse($this->validator->numeric(''));
        $this->assertFalse($this->validator->numeric('test'));
    }

    public function testMinWorksWhenValid()
    {
        // Assert
        $this->assertTrue($this->validator->min(11, 10));
        $this->assertTrue($this->validator->min(10, 10));
    }

    public function testMinWorksWhenInvalid()
    {
        // Assert
        $this->assertFalse($this->validator->min(9, 10));
        $this->assertFalse($this->validator->min('text', 10));
    }

    public function testMaxWorksWhenValid()
    {
        // Assert
        $this->assertTrue($this->validator->max(10, 11));
        $this->assertTrue($this->validator->max(10, 10));
        $this->assertTrue($this->validator->max('text', 5));
        $this->assertTrue($this->validator->max('text', 4));
    }

    public function testMaxWorksWhenInvalid()
    {
        // Assert
        $this->assertFalse($this->validator->max(11, 10));
        $this->assertFalse($this->validator->max('text', 3));
    }

    public function testBetweenWorksWhenValid()
    {
        // Assert
        $this->assertTrue($this->validator->between(15, 10, 20));
        $this->assertTrue($this->validator->between(10, 10, 20));
        $this->assertTrue($this->validator->between(20, 10, 20));
    }

    public function testBetweenWorksWhenInvalid()
    {
        // Assert
        $this->assertFalse($this->validator->between(9, 10, 20));
        $this->assertFalse($this->validator->between(21, 10, 20));
        $this->assertFalse($this->validator->between('text', 10, 20));
    }

    public function testBooleanWorksWhenValid()
    {
        // Assert
        $this->assertTrue($this->validator->boolean(true));
        $this->assertTrue($this->validator->boolean(false));
        $this->assertTrue($this->validator->boolean(1));
        $this->assertTrue($this->validator->boolean(0));
        $this->assertTrue($this->validator->boolean('1'));
        $this->assertTrue($this->validator->boolean('0'));
    }

    public function testBooleanWorksWhenInvalid()
    {
        // Assert
        $this->assertFalse($this->validator->boolean(2));
        $this->assertFalse($this->validator->boolean(-1));
        $this->assertFalse($this->validator->boolean('2'));
        $this->assertFalse($this->validator->boolean('-1'));
    }

    public function testEmailWorksWhenValid()
    {
        // Assert
        $this->assertTrue($this->validator->email('a@b.cc'));
    }

    public function testEmailWorksWhenInvalid()
    {
        // Assert
        $this->assertFalse($this->validator->email('a@b'));
        $this->assertFalse($this->validator->email('a@.c'));
        $this->assertFalse($this->validator->email('a.c'));
        $this->assertFalse($this->validator->email('@b.c'));
    }

    public function testEqualsFieldWorksWhenValid()
    {
        // Arrange
        $this->validator->validate(['one' => 1], []);

        // Assert
        $this->assertTrue($this->validator->equalsField(1, 'one'));
    }

    public function testEqualsFieldWorksWhenInvalid()
    {
        // Assert
        $this->assertFalse($this->validator->equalsField(2, 'one'));
        $this->assertFalse($this->validator->equalsField(2, 'nonexistant'));
    }
}