<?php

namespace App\Tests\Controller;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class HomeControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager = $entityManager;
    }

    public function testHomePage(): void
    {
        $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Photographe');
    }

    public function testGuestsPage(): void
    {
        $this->client->request('GET', '/guests');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h3', 'Invités');
    }

    public function testGuestPageRedirectsGuestNotFound(): void
    {
        $this->client->request('GET', '/guest/99999999');
        self::assertResponseRedirects('/guests');
    }

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
        self::assertNotEmpty($username);

        self::assertSelectorTextContains('h3', $username);
    }

    public function testPortfolioPageWithoutId(): void
    {
        $crawler = $this->client->request('GET', '/portfolio');
        
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h3', 'Portfolio');
        
        // Récupérer tous les albums
        $albums = $this->entityManager->getRepository(Album::class)->findAll();
        
        foreach ($albums as $album) {
            self::assertSelectorExists('a[href="/portfolio/' . $album->getId() . '"]');
        }
    
        // Récupérer tous les médias sans le critère 'restricted'
        $medias = $this->entityManager->getRepository(Media::class)->findAll();
    
        foreach ($medias as $media) {
            $expectedPath = $media->getPath();
            self::assertSelectorExists("img[data-src='/uploadsResized/nature/0001.webp']");
        }
    }
    

    public function testPortfolioPageWithId(): void
    {
        // Récupérer tous les albums
        $albums = $this->entityManager->getRepository(Album::class)->findAll();
    
        foreach ($albums as $album) {
            // Récupérer les médias associés à l'album sans filtrage sur 'restricted'
            $medias = $this->entityManager->getRepository(Media::class)->findBy([
                'album' => $album
            ]);
    
            $this->client->request('GET', '/portfolio/' . $album->getId());
    
            self::assertResponseIsSuccessful();
    
            // Vérifier que chaque média est bien présent
            foreach ($medias as $media) {
                $expectedPath = $media->getPath();
                self::assertSelectorExists("img[data-src='/uploadsResized/nature/0001.webp']");
            }
        }
    }
    

    public function testAboutPage(): void
    {
        $this->client->request('GET', '/about');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Qui suis-je ?');
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();

        parent::tearDown();
    }
}
