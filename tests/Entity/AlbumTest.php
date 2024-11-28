<?php

namespace App\Tests\Entity;

use App\Entity\Album;
use PHPUnit\Framework\TestCase;

class AlbumTest extends TestCase
{
    public function testGetId()
    {
        $album = new Album();

        $reflection = new \ReflectionClass($album);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($album, 1);

        $this->assertEquals(1, $album->getId());
    }

    public function testGetName()
    {
        $album = new Album();
        $album->setName('Test Album');

        $this->assertEquals('Test Album', $album->getName());
    }

    public function testSetName()
    {
        $album = new Album();
        $album->setName('Another Album');

        $this->assertEquals('Another Album', $album->getName());
    }
}
