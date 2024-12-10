<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Media;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    /*
    * Prépare le client et l'utilisateur administrateur pour les tests
    */
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

    /*
    *Teste l'affichage de la liste des médias
    */
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/media');
        $this->assertResponseIsSuccessful();
    }

    /*
    *Teste l'ajout d'un nouveau média
    */
    public function testAddMedia(): void
    {
        $this->client->request('GET', '/admin/media/add');
        $this->assertResponseIsSuccessful();
    
        // Créer un fichier temporaire valide
        $tempFilePath = tempnam(sys_get_temp_dir(), 'test_media_') . '.jpg';
        file_put_contents($tempFilePath, base64_decode('/9j/4AAQSkZJRgABAQEAAAAAAAD/4QAiRXhpZgAATU0A...')); // Contenu d'une image JPEG valide
        $uploadedFile = new UploadedFile(
            $tempFilePath,
            'test_image.jpg',
            'image/jpeg',
            null,
            true
        );
    
        // Soumettre le formulaire avec les données valides
        $this->client->submitForm('Ajouter', [
            'media[title]' => 'Test Media',
            'media[user]' => "", 
            'media[album]' => "", 
            'media[file]' => $uploadedFile,
        ]);
    
    
        $this->assertResponseRedirects('/admin/media');
    
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $media = $entityManager->getRepository(Media::class)->findOneBy(['title' => 'Test Media']);
        $this->assertNotNull($media, 'Le média n\'a pas été ajouté.');
        $this->assertEquals('Test Media', $media->getTitle());
    
        unlink($tempFilePath);
    }
    
    /*
    *Teste la suppression d'un média existant
    */
    public function testDeleteMedia(): void
    {
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    
        $media = new Media();
        $media->setTitle('Media to Delete');
        $media->setPath('test_path.txt'); 
        $entityManager->persist($media);
        $entityManager->flush();
    
        $mediaId = $media->getId();
        $this->assertNotNull($mediaId, 'L\'ID du média de test n\'a pas été généré.');
    
        // Supprime le média
        $this->client->request('GET', '/admin/media/delete/' . $mediaId);
        $this->assertResponseRedirects('/admin/media');
    
        // Vérifie que le média a été supprimé
        $deletedMedia = $entityManager->getRepository(Media::class)->find($mediaId);
        $this->assertNull($deletedMedia, 'Le média n\'a pas été supprimé.');
    }
    
    /*
    *Teste la suppression d'un média inexistant
    */
    public function testDeleteNonExistentMedia(): void
    {
        $nonExistentId = 9999;
    
        // Exécuter la requête de suppression pour un média inexistant
        $this->client->request('GET', '/admin/media/delete/' . $nonExistentId);
    
        // Vérifiez que la réponse est bien une erreur 404
        $this->assertResponseStatusCodeSame(404, 'La suppression d\'un média inexistant devrait renvoyer une erreur 404.');
    }
    
    

    /*
    *Nettoyage après les tests
    */
    protected function tearDown(): void
    {
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $mediaRepository = $entityManager->getRepository(Media::class);

        // Supprime les médias créés pendant les tests
        $testMedias = $mediaRepository->findBy(['title' => ['Test Media', 'Media to Delete']]);
        foreach ($testMedias as $media) {
            $entityManager->remove($media);
        }

        $entityManager->flush();

        parent::tearDown();
    }
}
