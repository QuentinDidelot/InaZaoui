<?php

namespace App\Tests\Controller\Admin;

use App\DataFixtures\AppFixtures;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        // Crée un client pour simuler des requêtes HTTP
        $this->client = static::createClient();
        // Récupère l'EntityManager pour interagir avec la base de données
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }


    public function testLoginPage(): void
    {
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Connexion');
    }

    public function testLogoutRedirectsUser(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'restricted' => false
        ]);
        self::assertNotNull($user);
    
        $this->client->loginUser($user);
        $this->client->request('GET', '/logout');
        self::assertResponseRedirects('/');  
        $this->client->followRedirect();
    
        self::assertSelectorExists('nav a[href="/login"]');  
    }
    

    public function testIncorrectUsername(): void
    {
        // Récupération d'un utilisateur non restreint
        $restrictedUser = $this->entityManager->getRepository(User::class)->findOneBy([
            'restricted' => false
        ]);
        self::assertNotNull($restrictedUser);
    
        $crawler = $this->client->request('GET', '/login');
    
        $form = $crawler->selectButton('Connexion')->form([
            '_username' => 'incorrectUsername',
            '_password' => 'password'
        ]);
    
        $this->client->submit($form);
        self::assertResponseRedirects('/login');
        $this->client->followRedirect();
    
        self::assertSelectorTextContains('div.alert-danger', 'Invalid credentials.');
    }
    
    public function testIncorrectPassword(): void
    {
        // Récupération d'un utilisateur non restreint
        $restrictedUser = $this->entityManager->getRepository(User::class)->findOneBy([
            'restricted' => false
        ]);
        self::assertNotNull($restrictedUser);

        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form([
            '_username' => $restrictedUser->getUsername(),
            '_password' => 'incorrectPassword'
        ]);
    
        $this->client->submit($form);
        self::assertResponseRedirects('/login');
        $this->client->followRedirect();
    
        self::assertSelectorTextContains('div.alert-danger', 'Invalid credentials.');
    }

    public function testRestrictedUserCannotLogin(): void
    {
        $restrictedUser = $this->entityManager->getRepository(User::class)->findOneBy([
            'restricted' => true
        ]);
        self::assertNotNull($restrictedUser);

        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form([
            '_username' => $restrictedUser->getUsername(),
            '_password' => 'password'
        ]);

        $this->client->submit($form);
        self::assertResponseRedirects('/login');

        $this->client->followRedirect();
        self::assertSelectorTextContains('div', 'Votre compte a été suspendu.');
    }
    

}
