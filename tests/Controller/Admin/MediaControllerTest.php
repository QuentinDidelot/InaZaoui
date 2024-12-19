<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Media;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
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
    
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    
        // Récupérer un utilisateur administrateur à partir de la base de données  
        $adminUser = $entityManager->getRepository(User::class)->findOneBy(['email' => 'ina@gmail.com']);
    
        // Vérifie que l'utilisateur existe dans la base de données de test  
        $this->assertNotNull($adminUser, 'Le compte administrateur n\'existe pas dans la base de données de test.');
    
        // Connecter l'utilisateur administrateur pour les tests  
        $this->client->loginUser($adminUser);
    }

    /*
    * Teste l'affichage de la liste des médias
    */
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/media');
        $this->assertResponseIsSuccessful();
    }

    /*
    * Teste l'ajout d'un nouveau média
    */
    public function testAddMedia(): void
    {
        $this->client->request('GET', '/admin/media/add');
        $this->assertResponseIsSuccessful();
    

        $tempFilePath = tempnam(sys_get_temp_dir(), 'testMedia3') . '.jpg';
        if (!copy('tests\images\landscape.jpg', $tempFilePath)) {
            throw new \Exception('Impossible de copier le fichier de test.');
        }

        $uploadedFile = new UploadedFile(
            $tempFilePath,
            'test_image.jpg',
            'image/jpeg',
            null,
            true
        );
    
        // Soumettre le formulaire avec les données valides
        $this->client->submitForm('Ajouter', [
            'media[title]' => 'TestMedia3',
            'media[user]' => "", 
            'media[album]' => "", 
            'media[file]' => $uploadedFile,
        ]);

        $this->assertResponseRedirects('/admin/media');
    
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $media = $entityManager->getRepository(Media::class)->findOneBy(['title' => 'TestMedia3']);
        $this->assertNotNull($media, 'Le média n\'a pas été ajouté.');
        $this->assertEquals('TestMedia3', $media->getTitle());

        unlink($tempFilePath);
    }
    
    /*
    * Teste la suppression d'un média existant
    */
    public function testDeleteMedia(): void
    {
    /** @var EntityManagerInterface $entityManager */        
    $entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    
        // Créer un média à supprimer
        $media = new Media();
        $media->setTitle('Media to Delete');
        $media->setPath('test_path.txt');
        $entityManager->persist($media);
        $entityManager->flush();
    
        $mediaId = $media->getId();
        $this->assertNotNull($mediaId, 'L\'ID du média de test n\'a pas été généré.');
    
        // Supprimer le média
        $this->client->request('GET', '/admin/media/delete/' . $mediaId);
        $this->assertResponseRedirects('/admin/media');
    
        // Vérifier que le média a été supprimé
        $deletedMedia = $entityManager->getRepository(Media::class)->find($mediaId);
        $this->assertNull($deletedMedia, 'Le média n\'a pas été supprimé.');
    }
    
    /*
    * Teste la suppression d'un média inexistant
    */
    public function testDeleteNonExistentMedia(): void
    {
        $nonExistentId = 9999;
    
        // Exécuter la requête de suppression pour un média inexistant
        $this->client->request('GET', '/admin/media/delete/' . $nonExistentId);
    
        // Vérifier que la réponse est une erreur 404
        $this->assertResponseStatusCodeSame(404, 'La suppression d\'un média inexistant devrait renvoyer une erreur 404.');
    }
    
    /*
    * Nettoyage après les tests
    */
    protected function tearDown(): void  
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $mediaRepository = $entityManager->getRepository(Media::class);

        // Supprimer les médias créés pendant les tests  
        $testMedias = $mediaRepository->findBy(['title' => ['Test Media', 'Media to Delete']]);
        foreach ($testMedias as $media) {
            $entityManager->remove($media);
        }

        $entityManager->flush();

        parent::tearDown();
    }
}