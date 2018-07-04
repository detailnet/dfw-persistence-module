<?php

namespace DetailTest\Persistence\Options;

use Detail\Persistence\Options\DoctrineOptions;
use Detail\Persistence\Options\ModuleOptions;

class ModuleOptionsTest extends OptionsTestCase
{
    /**
     * @var ModuleOptions
     */
    protected $options;

    protected function setUp()
    {
        $this->options = $this->getOptions(
            ModuleOptions::CLASS,
            [
                'getDoctrine',
                'setDoctrine',
            ]
        );
    }

    public function testDoctrineRegisterTypeCanBeSet(): void
    {
        $doctrine = ['register_uuid_type' => true];

        $this->assertInstanceOf(DoctrineOptions::CLASS, $this->options->getDoctrine());
        $this->assertFalse($this->options->getDoctrine()->registerUuidType());

        $this->options->setDoctrine($doctrine);

        $this->assertTrue($this->options->getDoctrine()->registerUuidType());
    }
}
