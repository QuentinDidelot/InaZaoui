<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixturesTest extends KernelTestCase
{
    private $entityManager;
    private UserPasswordHasherInterface $userPasswordHasherInterface;

    // Appelée avant chaque test pour initialiser l'entité manager
    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->userPasswordHasherInterface = self::getContainer()->get(UserPasswordHasherInterface::class);
    }


    public function testLoad() : void 
    {

        $purger = new ORMPurger($this->entityManager);
        $purger->purge();

        $fixtures = new AppFixtures($this->userPasswordHasherInterface);
        $fixtures->load($this->entityManager);

        // Vérifie que les albums ont été créés correctement
        $album1 = $this->entityManager->getRepository(Album::class)->findOneBy(['name' => 'Nature']);
        $this->assertNotNull($album1);
        $this->assertEquals('Nature', $album1->getName());


    }

    /*
    * Appelée après chaque test pour nettoyer
    */
    protected function tearDown(): void
    {
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
