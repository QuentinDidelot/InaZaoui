<?php 

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
    

    // public function testUpgradePasswordSuccess(): void
    // {
    //     $user = new User();
    //     $user->setUsername('testuser'); 
    //     $user->setEmail('testuser@example.com');
    //     $user->setPassword('oldPassword');  
        
    //     $this->userRepository->upgradePassword($user, 'newHashedPassword');
        
    //     $this->assertEquals('newHashedPassword', $user->getPassword());
    // }
    
    
    public function testUpgradePasswordThrowsExceptionForInvalidUser(): void
    {
        // Crée un utilisateur non valide
        $invalidUser = $this->createMock(PasswordAuthenticatedUserInterface::class);

        $this->expectException(UnsupportedUserException::class);
        
        // Appelle la méthode upgradePassword avec un mauvais type d'utilisateur
        $this->userRepository->upgradePassword($invalidUser, 'newHashedPassword');
    }

    
    
}
