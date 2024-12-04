<?php

namespace App\Tests\Controller\Admin;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class GuestControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        // Initialisation du client pour interagir avec l'application Symfony
        $this->client = static::createClient();
    }

    /**
     * Utiliser un utilisateur administrateur préexistant dans les fixtures
     */
    private function getAdminUser(): User
    {
        return $this->getEntityManager()->getRepository(User::class)->findOneBy(['email' => 'ina@gmail.com']);
    }

    /**
     * Utiliser un utilisateur invité préexistant dans les fixtures
     */
    private function getGuestUser(): User
    {
        return $this->getEntityManager()->getRepository(User::class)->findOneBy(['email' => 'john@gmail.com']);
    }

    /**
     * Utiliser un utilisateur restreint préexistant dans les fixtures
     */
    private function getRestrictedUser(): User
    {
        return $this->getEntityManager()->getRepository(User::class)->findOneBy(['email' => 'kaidan@gmail.com']);
    }

    /**
     * Accéder à l'EntityManager pour manipuler la base de données
     */
    private function getEntityManager(): EntityManagerInterface
    {
        return $this->client->getContainer()->get('doctrine')->getManager();
    }

    /*
    * Teste la page d'index des invités
    */
    public function testIndex(): void
    {
        // Connexion avec l'utilisateur admin
        $this->client->loginUser($this->getAdminUser());
    
        $this->client->request('GET', '/admin/guest');

        $this->assertResponseIsSuccessful();
    }
    
    public function testAddGuest(): void
    {
        $this->client->loginUser($this->getAdminUser());
        
        $this->client->request('GET', '/admin/guest/add');
        $this->assertResponseIsSuccessful();
    
        $this->client->submitForm('Ajouter', [
            'guest[username]' => 'Miranda',
            'guest[email]' => 'miranda@gmail.com',
            'guest[password][first]' => 'password', 
            'guest[password][second]' => 'password',
            'guest[description]' => 'Invitées',
        ]);
    
        // Vérifiez la redirection correcte
        $this->assertResponseRedirects('/admin/guest');
    
        $this->client->followRedirect();
    
        $entityManager = $this->getEntityManager();
        $userRepo = $entityManager->getRepository(User::class);
        $newGuest = $userRepo->findOneBy(['email' => 'miranda@gmail.com']);
    
        $this->assertNotNull($newGuest);
        $this->assertEquals('Miranda', $newGuest->getUsername());
        $this->assertEquals('miranda@gmail.com', $newGuest->getEmail());
    }
    /**
     * Teste le blocage d'un invité
     */
    public function testBlockGuest(): void
    {
        $this->client->loginUser($this->getAdminUser());

        $guest = $this->getGuestUser();

        // Blocage de l'invité
        $this->client->request('GET', '/admin/guest/block/' . $guest->getId());
        $this->assertResponseRedirects('/admin/guest');

        // Vérification que l'invité est bien bloqué
        $this->getEntityManager()->refresh($guest);
        $this->assertTrue($guest->isRestricted());
    }

    /**
     * Teste le déblocage d'un invité
     */
    public function testUnblockGuest(): void
    {
        // Connexion avec l'utilisateur admin
        $this->client->loginUser($this->getAdminUser());

        $restrictedGuest = $this->getRestrictedUser();

        // Déblocage de l'invité
        $this->client->request('GET', '/admin/guest/unblock/' . $restrictedGuest->getId());
        $this->assertResponseRedirects('/admin/guest');

        // Vérification que l'invité est bien débloqué
        $this->getEntityManager()->refresh($restrictedGuest);
        $this->assertFalse($restrictedGuest->isRestricted());
    }


    public function testDeleteGuest(): void
    {
        $entityManager = $this->getEntityManager();
        $userRepo = $entityManager->getRepository(User::class);

        // Créer un utilisateur pour le test
        $guest = new User();
        $guest->setUsername('TestUser');
        $guest->setEmail('testuser@gmail.com');
        $guest->setPassword('password');
        $guest->setRoles(['ROLE_USER']);
        $entityManager->persist($guest);
        $entityManager->flush();

        $this->assertNotNull($userRepo->findOneBy(['email' => 'testuser@gmail.com']));

        // Supprimer l'utilisateur via la méthode DELETE
        $this->client->loginUser($this->getAdminUser());
        $this->client->request('DELETE', '/admin/guest/delete/' . $guest->getId());

        $this->assertNull($userRepo->findOneBy(['email' => 'testuser@gmail.com']));

        $this->assertResponseRedirects('/admin/guest');
    }


    protected function tearDown(): void
    {
        $entityManager = $this->getEntityManager();
        $userRepo = $entityManager->getRepository(User::class);
    
        $newGuest = $userRepo->findOneBy(['email' => 'miranda@gmail.com']);
        if ($newGuest) {
            $entityManager->remove($newGuest);
            $entityManager->flush();
        }
    
        parent::tearDown();
    }
    
}
