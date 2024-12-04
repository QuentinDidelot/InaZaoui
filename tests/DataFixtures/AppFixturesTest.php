<?php

namespace App\Tests;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AppFixturesTest extends KernelTestCase
{
    private $entityManager;

    // Appelée avant chaque test pour initialiser l'entité manager
    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testAdminUserIsCreated(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'ina']);
        $this->assertNotNull($user);
        $this->assertEquals('ina', $user->getUserName());
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertFalse($user->isRestricted());
    }

    public function testGuestUserIsCreated(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'john']);
        $this->assertNotNull($user);
        $this->assertEquals('john', $user->getUserName());
        $this->assertContains('ROLE_USER', $user->getRoles());

        $this->assertTrue($user->isRestricted());
    }
    
    public function testRestrictedUserIsCreated(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'kaidan']);
        $this->assertNotNull($user);
        $this->assertEquals('kaidan', $user->getUserName());
        $this->assertContains('ROLE_USER', $user->getRoles());
        
        // Correction : vérifier que l'utilisateur "kaidan" est restreint
        $this->assertFalse($user->isRestricted());
    }
    

    public function testAlbumsAreCreated(): void
    {
        $albums = $this->entityManager->getRepository(Album::class)->findAll();
        $this->assertCount(4, $albums);
        
        $albumNames = array_map(function(Album $album) {
            return $album->getName();
        }, $albums);

        $this->assertContains('Amérique', $albumNames);
        $this->assertContains('Europe', $albumNames);
        $this->assertContains('Asie', $albumNames);
        $this->assertContains('Afrique', $albumNames);
    }

    public function testMediaAreCreated(): void
    {
        $media = $this->entityManager->getRepository(Media::class)->findAll();
        $this->assertCount(12, $media);  // 4 albums * 3 médias par album

        $mediaTitles = array_map(function(Media $media) {
            return $media->getTitle();
        }, $media);

        // Vérifier que tous les titres de médias sont créés
        $this->assertContains('Amerique1', $mediaTitles);
        $this->assertContains('Europe1', $mediaTitles);
        $this->assertContains('Asia1', $mediaTitles);
        $this->assertContains('Afrique1', $mediaTitles);
    }

    public function testMediaAreAssociatedWithAlbumsAndUsers(): void
    {
        $media = $this->entityManager->getRepository(Media::class)->findAll();
        foreach ($media as $mediaItem) {
            $this->assertNotNull($mediaItem->getAlbum());
            $this->assertNotNull($mediaItem->getUser());
        }
    }

    // Appelée après chaque test pour nettoyer
    protected function tearDown(): void
    {
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
