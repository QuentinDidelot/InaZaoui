<?php 
// tests/Repository/UserRepositoryTest.php

// tests/Repository/UserRepositoryTest.php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserRepositoryTest extends KernelTestCase
{
    private $userRepository;
    private $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        
        // Utilise le conteneur pour récupérer le UserRepository
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        
        // Récupère l'EntityManager si tu en as besoin pour d'autres tests
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }
    

    public function testUpgradePasswordSuccess(): void
    {
        // Crée une instance de User avec un username et un mot de passe initial
        $user = new User();
        $user->setUsername('testuser');  // Définir un username valide
        $user->setEmail('testuser@example.com');
        $user->setPassword('oldPassword');  // Définir un mot de passe initial
        
        // Appelle la méthode upgradePassword pour mettre à jour le mot de passe
        $this->userRepository->upgradePassword($user, 'newHashedPassword');
        
        // Vérifie que le mot de passe a bien été mis à jour
        $this->assertEquals('newHashedPassword', $user->getPassword());
    }
    
    
    public function testUpgradePasswordThrowsExceptionForInvalidUser(): void
{
    // Crée un utilisateur non valide
    $invalidUser = $this->createMock(PasswordAuthenticatedUserInterface::class);

    // S'attendre à ce qu'une exception soit lancée
    $this->expectException(UnsupportedUserException::class);
    
    // Appelle la méthode upgradePassword avec un mauvais type d'utilisateur
    $this->userRepository->upgradePassword($invalidUser, 'newHashedPassword');
}

    
    
}
