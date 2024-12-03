<?php

namespace App\Tests\Controller\Admin;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\AlbumRepository;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;


class AlbumControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    // Prépare le client et l'utilisateur administrateur pour les tests
    protected function setUp(): void
    {
        $this->client = static::createClient();

        // Rechercher un utilisateur administrateur de test
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $adminUser = $entityManager->getRepository(User::class)->findOneBy(['email' => 'ina@gmail.com']);

        // Vérifiez si l'utilisateur existe dans la base de données de test
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
        $albumRepository = static::getContainer()->get(AlbumRepository::class);
        $album = $albumRepository->findOneBy(['name'=>'Test Album' ]);

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
        $albumRepository = static::getContainer()->get(AlbumRepository::class);
        $album = $albumRepository->findOneBy(["name"=>"Updated Album"]);

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
