<?php

namespace Detail\Persistence\Doctrine\ODM\Id;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Id\AbstractIdGenerator;

use Rhumsaa\Uuid\Uuid;

class UuidGenerator extends AbstractIdGenerator
{
    /**
     * Generates an identifier for a document.
     *
     * @param \Doctrine\ODM\MongoDB\DocumentManager $dm
     * @param object $document
     * @return Uuid
     */
    public function generate(DocumentManager $dm, $document)
    {
        return Uuid::uuid4();
    }
}
