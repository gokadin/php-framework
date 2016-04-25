<?php

namespace Tests\Container;

use Library\Container\Container;
use Tests\BaseTest;
use Tests\TestData\Container\ConcreteEmptyConstructor;
use Tests\TestData\Container\ConcreteImplementingInterfaceOne;
use Tests\TestData\Container\ConcreteImplementingInterfaceTwo;
use Tests\TestData\Container\ConcreteNoConstructor;
use Tests\TestData\Container\ConcreteNoTypeHintDefault;
use Tests\TestData\Container\ConcreteNoTypeHintNoDefault;
use Tests\TestData\Container\ConcreteWithDefaultInterface;
use Tests\TestData\Container\ConcreteWithMixedButValid;
use Tests\TestData\Container\ConcreteWithMixedButValidIncludingInterfaces;
use Tests\TestData\Container\ConcreteWithMultipleComplexTypeHint;
use Tests\TestData\Container\ConcreteWithMultipleSimpleTypeHint;
use Tests\TestData\Container\ConcreteWithSimpleTypeHint;
use Tests\TestData\Container\ConcreteWithMethodParameters;
use Tests\TestData\Container\InterfaceOne;

class ContainerTest extends BaseTest
{
    public function testResolveInstanceWhenInstanceIsRegistered()
    {
        // Arrange
        $container = new Container();
        $container->registerInstance('test', new ConcreteNoConstructor());
        $container->registerInstance('test2', new ConcreteEmptyConstructor());

        // Act
        $resolved = $container->resolveInstance('test');

        // Assert
        $this->assertTrue($resolved instanceof ConcreteNoConstructor);
    }

    /**
     * @expectedException \Library\Container\ContainerException
     */
    public function testResolveInstanceWhenInstanceIsNotRegistered()
    {
        // Arrange
        $container = new Container();
        $container->registerInstance('test2', new ConcreteNoConstructor());

        // Act
        $resolved = $container->resolveInstance('test');

        // Assert
        $this->assertNull($resolved);
    }

    public function testResolveForConcreteWithoutConstructor()
    {
        // Arrange
        $container = new Container();

        // Act
        $resolved = $container->resolve(ConcreteNoConstructor::class);

        // Assert
        $this->assertTrue($resolved instanceof ConcreteNoConstructor);
    }

    public function testResolveForConcreteWithEmptyConstructor()
    {
        // Arrange
        $container = new Container();

        // Act
        $resolved = $container->resolve(ConcreteEmptyConstructor::class);

        // Assert
        $this->assertTrue($resolved instanceof ConcreteEmptyConstructor);
    }

    public function testResolveForConcreteWithNonTypeHintedDefaultParameter()
    {
        // Arrange
        $container = new Container();

        // Act
        $resolved = $container->resolve(ConcreteNoTypeHintDefault::class);

        // Assert
        $this->assertTrue($resolved instanceof ConcreteNoTypeHintDefault);
        $this->assertEquals(3, $resolved->getA());
    }

    /**
     * @expectedException \Library\Container\ContainerException
     */
    public function testResolveForConcreteWithNonTypeHintedNonDefaultParameter()
    {
        // Arrange
        $container = new Container();

        // Act
        $resolved = $container->resolve(ConcreteNoTypeHintNoDefault::class);

        // Assert
        $this->assertNull($resolved);
    }

    public function testResolveForConcreteWithSimpleTypeHintedParameter()
    {
        // Arrange
        $container = new Container();

        // Act
        $resolved = $container->resolve(ConcreteWithSimpleTypeHint::class);

        // Assert
        $this->assertTrue($resolved instanceof ConcreteWithSimpleTypeHint);
        $this->assertTrue($resolved->getA() instanceof ConcreteNoConstructor);
    }

    public function testResolveForConcreteWithMultipleSimpleTypeHintedParameters()
    {
        // Arrange
        $container = new Container();

        // Act
        $resolved = $container->resolve(ConcreteWithMultipleSimpleTypeHint::class);

        // Assert
        $this->assertTrue($resolved instanceof ConcreteWithMultipleSimpleTypeHint);
        $this->assertTrue($resolved->getA() instanceof ConcreteNoConstructor);
        $this->assertTrue($resolved->getB() instanceof ConcreteNoConstructor);
        $this->assertTrue($resolved->getC() instanceof ConcreteNoConstructor);
    }

    public function testResolveForConcreteWithMultipleComplexTypeHintedParameters()
    {
        // Arrange
        $container = new Container();

        // Act
        $resolved = $container->resolve(ConcreteWithMultipleComplexTypeHint::class);

        // Assert
        $this->assertTrue($resolved instanceof ConcreteWithMultipleComplexTypeHint);
        $this->assertTrue($resolved->getA() instanceof ConcreteWithSimpleTypeHint);
        $this->assertTrue($resolved->getB() instanceof ConcreteWithMultipleSimpleTypeHint);
    }

    public function testResolveForConcreteWithMixedButValidParameters()
    {
        // Arrange
        $container = new Container();

        // Act
        $resolved = $container->resolve(ConcreteWithMixedButValid::class);

        // Assert
        $this->assertTrue($resolved instanceof ConcreteWithMixedButValid);
        $this->assertTrue($resolved->getA() instanceof ConcreteWithMultipleComplexTypeHint);
        $this->assertTrue($resolved->getB() instanceof ConcreteNoTypeHintDefault);
        $this->assertEquals(3, $resolved->getC());
    }

    public function testResolveForInterface()
    {
        // Arrange
        $container = new Container();
        $container->registerInterface(InterfaceOne::class, ConcreteImplementingInterfaceOne::class);

        // Act
        $resolved = $container->resolve(InterfaceOne::class);

        // Assert
        $this->assertTrue($resolved instanceof ConcreteImplementingInterfaceOne);
    }

    public function testResolveForInterfaceWithMixedButValidParameters()
    {
        // Arrange
        $container = new Container();
        $container->registerInterface(InterfaceOne::class, ConcreteImplementingInterfaceOne::class);

        // Act
        $resolved = $container->resolve(ConcreteWithMixedButValidIncludingInterfaces::class);

        // Assert
        $this->assertTrue($resolved instanceof ConcreteWithMixedButValidIncludingInterfaces);
        $this->assertTrue($resolved->getA() instanceof ConcreteImplementingInterfaceOne);
        $this->assertTrue($resolved->getB() instanceof ConcreteWithMultipleComplexTypeHint);
        $this->assertEquals(3, $resolved->getC());
    }

    /**
     * @expectedException \Library\Container\ContainerException
     */
    public function testResolveForInterfaceWhenNotRegistered()
    {
        // Arrange
        $container = new Container();

        // Act
        $resolved = $container->resolve(InterfaceOne::class);

        // Assert
        $this->assertNull($resolved);
    }

    public function testResolveForInterfaceWhenNotRegisteredButWithDefault()
    {
        // Arrange
        $container = new Container();

        // Act
        $resolved = $container->resolve(ConcreteWithDefaultInterface::class);

        // Assert
        $this->assertTrue($resolved instanceof ConcreteWithDefaultInterface);
        $this->assertNull($resolved->getA());
    }

    public function testResolveForConcreteWhenAParameterIsRegisteredAsInstance()
    {
        // Arrange
        $container = new Container();
        $container->registerInstance('test', new ConcreteNoConstructor());

        // Act
        $resolved = $container->resolve(ConcreteWithSimpleTypeHint::class);

        // Assert
        $this->assertTrue($resolved instanceof ConcreteWithSimpleTypeHint);
        $this->assertTrue($resolved->getA() instanceof ConcreteNoConstructor);
    }

    public function testResolveMethodParametersWithoutParameters()
    {
        // Arrange
        $container = new Container();

        // Act
        $resolvedParameters = $container->resolveMethodParameters(ConcreteWithMethodParameters::class, 'noParameters');

        // Assert
        $this->assertEquals(0, sizeof($resolvedParameters));
    }

    public function testResolveMethodParametersWithSimpleDefault()
    {
        // Arrange
        $container = new Container();

        // Act
        $resolvedParameters = $container->resolveMethodParameters(ConcreteWithMethodParameters::class, 'withDefault');

        // Assert
        $this->assertEquals(1, sizeof($resolvedParameters));
        $this->assertEquals(3, $resolvedParameters[0]);
    }

    /**
     * @expectedException \Library\Container\ContainerException
     */
    public function testResolveMethodParametersWithSimpleWithoutDefault()
    {
        // Arrange
        $container = new Container();

        // Act
        $resolvedParameters = $container->resolveMethodParameters(ConcreteWithMethodParameters::class, 'withoutDefault');

        // Assert
        $this->assertEquals(0, sizeof($resolvedParameters));
    }

    public function testResolveMethodParametersWithSimpleTypeHint()
    {
        // Arrange
        $container = new Container();

        // Act
        $resolvedParameters = $container->resolveMethodParameters(ConcreteWithMethodParameters::class, 'withConcrete');
        $class = new ConcreteWithMethodParameters();
        $value = call_user_func_array([$class, 'withConcrete'], $resolvedParameters);

        // Assert
        $this->assertEquals(1, sizeof($resolvedParameters));
        $this->assertTrue($value instanceof ConcreteNoConstructor);
    }

    public function testResolveMethodParametersWithInterface()
    {
        // Arrange
        $container = new Container();
        $container->registerInterface(InterfaceOne::class, ConcreteImplementingInterfaceOne::class);

        // Act
        $resolvedParameters = $container->resolveMethodParameters(ConcreteWithMethodParameters::class, 'withInterface');
        $class = new ConcreteWithMethodParameters();
        $value = call_user_func_array([$class, 'withInterface'], $resolvedParameters);

        // Assert
        $this->assertEquals(1, sizeof($resolvedParameters));
        $this->assertTrue($value instanceof ConcreteImplementingInterfaceOne);
    }

    public function testResolveMethodParametersWithInterfaceDefault()
    {
        // Arrange
        $container = new Container();

        // Act
        $resolvedParameters = $container->resolveMethodParameters(ConcreteWithMethodParameters::class, 'withDefaultInterface');
        $class = new ConcreteWithMethodParameters();
        $value = call_user_func_array([$class, 'withDefaultInterface'], $resolvedParameters);

        // Assert
        $this->assertEquals(1, sizeof($resolvedParameters));
        $this->assertNull($value);
    }
}