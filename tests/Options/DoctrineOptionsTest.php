<?php

namespace DetailTest\Persistence\Options;

use Detail\Persistence\Options\DoctrineOptions;

class DoctrineOptionsTest extends OptionsTestCase
{
    /**
     * @var DoctrineOptions
     */
    protected $options;

    protected function setUp()
    {
        $this->options = new DoctrineOptions();
    }

    public function testDoctrineRegisterUuidTypeCanBeSet(): void
    {
        $this->assertFalse($this->options->registerUuidType());

        $this->options->setRegisterUuidType(true);

        $this->assertTrue($this->options->registerUuidType());
    }

    public function testDoctrineDatetimeNoTzTypeCanBeSet(): void
    {
        $this->assertFalse($this->options->registerDatetimeNoTzType());

        $this->options->setRegisterDatetimeNoTzType(true);

        $this->assertTrue($this->options->registerDatetimeNoTzType());
    }

    public function testDoctrineDatetimeImmutableNoTzTypeCanBeSet(): void
    {
        $this->assertFalse($this->options->registerDatetimeImmutableNoTzType());

        $this->options->setRegisterDatetimeImmutableNoTzType(true);

        $this->assertTrue($this->options->registerDatetimeImmutableNoTzType());
    }

    public function testDoctrineTraversableHashTypeCanBeSet(): void
    {
        $this->assertFalse($this->options->registerTraversableHashType());

        $this->options->setRegisterTraversableHashType(true);

        $this->assertTrue($this->options->registerTraversableHashType());
    }
}
