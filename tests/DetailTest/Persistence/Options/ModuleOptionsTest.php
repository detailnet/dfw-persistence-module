<?php

namespace DetailTest\Persistence\Options;

class ModuleOptionsTest extends OptionsTestCase
{
    /**
     * @var \Detail\Persistence\Options\ModuleOptions
     */
    protected $options;

    protected function setUp()
    {
        $this->options = $this->getOptions(
            'Detail\Persistence\Options\ModuleOptions',
            array(
                'getDoctrine',
                'setDoctrine',
            )
        );
    }

    public function testDoctrineRegisterTypeCanBeSet()
    {
        $doctrine = array('register_uuid_type' => true);

        $this->assertInstanceOf('Detail\Persistence\Options\DoctrineOptions', $this->options->getDoctrine());
        $this->assertFalse($this->options->getDoctrine()->registerUuidType());

        $this->options->setDoctrine($doctrine);

        $this->assertTrue($this->options->getDoctrine()->registerUuidType());
    }

    public function testDoctrineDatetimeNoTzTypeCanBeSet()
    {
        $doctrine = array('register_datetime_no_tz_type' => true);

        $this->assertInstanceOf('Detail\Persistence\Options\DoctrineOptions', $this->options->getDoctrine());
        $this->assertFalse($this->options->getDoctrine()->registerDatetimeNoTzType());

        $this->options->setDoctrine($doctrine);

        $this->assertTrue($this->options->getDoctrine()->registerDatetimeNoTzType());
    }

    public function testDoctrineDatetimeImmutableNoTzTypeCanBeSet()
    {
        $doctrine = array('register_datetime_immutable_no_tz_type' => true);

        $this->assertInstanceOf('Detail\Persistence\Options\DoctrineOptions', $this->options->getDoctrine());
        $this->assertFalse($this->options->getDoctrine()->registerDatetimeImmutableNoTzType());

        $this->options->setDoctrine($doctrine);

        $this->assertTrue($this->options->getDoctrine()->registerDatetimeImmutableNoTzType());
    }
}
