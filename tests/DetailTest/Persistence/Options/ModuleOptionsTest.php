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
//                'getEncryptor',
//                'setEncryptor',
            )
        );
    }

//    public function testEncryptorCanBeSet()
//    {
//        $encryptor = 'Some\Encryptor\Class';
//
//        $this->assertNull($this->options->getEncryptor());
//
//        $this->options->setEncryptor($encryptor);
//
//        $this->assertEquals($encryptor, $this->options->getEncryptor());
//    }
}
