<?php

namespace Detail\Persistence\Doctrine\ODM\Id;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Id\AbstractIdGenerator;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UuidGenerator extends AbstractIdGenerator
{
    /**
     * Generates an identifier for a document.
     *
     * @param DocumentManager $dm
     * @param object $document
     * @return UuidInterface
     */
    public function generate(DocumentManager $dm, object $document)
    {
        return Uuid::uuid4();
    }
}
