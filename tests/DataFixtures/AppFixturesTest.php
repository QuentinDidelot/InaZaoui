<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use App\Entity\Album;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixturesTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $userPasswordHasherInterface;
    private ParameterBagInterface $parameterBag;

    protected function setUp(): void
    {
        self::bootKernel();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager = $entityManager;

        /** @var UserPasswordHasherInterface $userPasswordHasher */
        $userPasswordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $this->userPasswordHasherInterface = $userPasswordHasher;

        /** @var ParameterBagInterface $parameterBag */
        $parameterBag = self::getContainer()->get('parameter_bag');
        $this->parameterBag = $parameterBag;
    }

    public function testLoad(): void
    {
        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
    
        // Création de l'instance de AppFixtures avec l'injection de dépendances
        $fixtures = new AppFixtures($this->userPasswordHasherInterface, $this->parameterBag);
        $fixtures->load($this->entityManager);
    
        // Vérifie que les albums ont été créés correctement
        $album1 = $this->entityManager->getRepository(Album::class)->findOneBy(['name' => 'Nature']);
        $this->assertNotNull($album1);
        $this->assertEquals('Nature', $album1->getName());
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();
    }
}
