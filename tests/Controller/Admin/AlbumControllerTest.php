<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Album;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface; // Import des interfaces nécessaires
use Doctrine\ORM\EntityRepository;

class AlbumControllerTest extends WebTestCase  
{
    private KernelBrowser $client;

    /** @var EntityRepository<Album> */
    private EntityRepository $albumRepository;
    
    protected function setUp(): void  
    {
        $this->client = static::createClient();
        
        // Récupération de l'EntityManager pour le test  
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        
        // Récupération du repository spécifique de type AlbumRepository  
        $this->albumRepository = $entityManager->getRepository(Album::class);
    
        // Vérifier si l'albumRepository est bien typé  
        $this->assertInstanceOf(AlbumRepository::class, $this->albumRepository);
    
        // Récupérer l'utilisateur administrateur  
        $adminUser = $entityManager->getRepository(User::class)->findOneBy(['email' => 'ina@gmail.com']);
        
        // Vérifier si l'utilisateur existe dans la base de données de test  
        $this->assertNotNull($adminUser, 'Le compte administrateur n\'existe pas dans la base de données de test.');
        
        $this->client->loginUser($adminUser);
    }
    
    

    // Teste l'affichage de la liste des albums
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/album');
        $this->assertResponseIsSuccessful();
    }

    // Teste l'ajout d'un nouvel album
    public function testAddAlbum(): void
    {
        $this->client->request('GET', '/admin/album/add');
        $this->assertResponseIsSuccessful();
        $this->client->submitForm('Ajouter', [
            'album[name]' => 'Test Album',
        ]);
        $this->assertResponseRedirects('/admin/album');
    }

    // Teste la mise à jour d'un album existant
    public function testUpdateAlbum(): void
    {
        // Utilisation de l'albumRepository injecté
        $album = $this->albumRepository->findOneBy(['name' => 'Test Album']);
        $this->assertNotNull($album, 'L\'album "Test Album" n\'a pas été trouvé dans la base de données.');

        $this->client->request('GET', '/admin/album/update/' . $album->getId());
        $this->assertResponseIsSuccessful();
        $this->client->submitForm('Modifier', [
            'album[name]' => 'Updated Album'
        ]);
        $this->assertResponseRedirects('/admin/album');
    }

    // Teste la suppression d'un album existant
    public function testDeleteAlbum(): void
    {
        // Utilisation de l'albumRepository injecté
        $album = $this->albumRepository->findOneBy(["name" => "Updated Album"]);
        $this->assertNotNull($album, 'L\'album "Updated Album" n\'a pas été trouvé dans la base de données.');

        $this->client->request('GET', '/admin/album/delete/' . $album->getId());
        $this->assertResponseRedirects('/admin/album');
    }

    // Teste la suppression d'un album inexistant
    public function testDeleteNonExistentAlbum(): void
    {
        // ID d'un album qui n'existe pas
        $nonExistentId = 9999;

        $this->client->request('GET', '/admin/album/delete/' . $nonExistentId);

        $this->assertResponseRedirects('/admin/media');
    }
}
