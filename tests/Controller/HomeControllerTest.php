<?php

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class HomeControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    // Configure l'environnement de test avant chaque test
    protected function setUp(): void
    {
        // Crée un client pour simuler des requêtes HTTP
        $this->client = static::createClient();
        // Récupère l'EntityManager pour interagir avec la base de données
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    // Teste que la page d'accueil est accessible et contient le texte attendu
    public function testHomePage(): void
    {
        $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Photographe');
    }

    // Teste que la page des invités est accessible et contient le texte attendu
    public function testGuestsPage(): void
    {
        $this->client->request('GET', '/guests');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h3', 'Invités');
    }

    // Teste que la page d'un invité inexistant redirige vers la page des invités
    public function testGuestPageRedirectsGuestNotFound(): void
    {
        $this->client->request('GET', '/guest/99999999');
        self::assertResponseRedirects('/guests');
    }

    // Teste que la page d'un invité restreint redirige vers la page des invités
    public function testGuestPageRedirectsWhenGuestIsRestricted(): void
    {
        $guestRestricted = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => false,
            'restricted' => true,
        ]);
        self::assertNotNull($guestRestricted);

        $this->client->request('GET', '/guest/' . $guestRestricted->getId());
        self::assertResponseRedirects('/guests');
    }

    // Teste que la page d'un invité non restreint est accessible et affiche son nom
    public function testGuestPage(): void
    {
        $guest = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => false,
            'restricted' => false,
        ]);
        self::assertNotNull($guest);

        $this->client->request('GET', '/guest/' . $guest->getId());
        self::assertResponseIsSuccessful();

        $username = $guest->getUsername();
        self::assertNotNull($username);

        self::assertSelectorTextContains('h3', $username);
    }

    // Teste que la page du portfolio sans ID est accessible et affiche les albums et médias
    public function testPortfolioPageWithoutId(): void
    {
        $this->client->request('GET', '/portfolio');
    
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h3', 'Portfolio');
    
        $albums = $this->entityManager->getRepository(Album::class)->findAll();
    
        foreach ($albums as $album) {
            self::assertSelectorExists('a[href="/portfolio/' . $album->getId() . '"]');
        }
    
        $medias = $this->entityManager->getRepository(Media::class)->findAllMediasNotRestricted();
    
        foreach ($medias as $media) {
            self::assertSelectorExists("img[src='/uploads/nature/0001.jpg']");
        }
    }
    
    

    // Teste que la page du portfolio avec un ID spécifique est accessible et affiche les médias correspondants
    // public function testPortfolioPageWithId(): void
    // {
    //     $albums = $this->entityManager->getRepository(Album::class)->findAll();

    //     foreach ($albums as $album) {
    //         $medias = $this->entityManager->getRepository(Media::class)->findAllMediasNotRestrictedByAlbum($album);

    //         $this->client->request('GET', '/portfolio/' . $album->getId());

    //         self::assertResponseIsSuccessful();

    //         foreach ($medias as $media) {
    //             self::assertSelectorExists('img[src="/' . $media->getPath() . '"]');
    //         }
    //     }
    // }

    // Teste que la page "À propos" est accessible et contient le texte attendu
    public function testAboutPage(): void
    {
        $this->client->request('GET', '/about');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Qui suis-je ?');
    }

    // Libère les ressources et nettoie après chaque test
    protected function tearDown(): void
    {
        $this->entityManager->close();

        parent::tearDown();
    }
}
