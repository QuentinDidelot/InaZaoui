<?php

namespace App\Tests\Entity;

use App\Entity\Media;
use App\Entity\User;
use App\Entity\Album;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaTest extends TestCase
{
    private Media $media;

    protected function setUp(): void
    {
        $this->media = new Media();
    }

    /**
     * Teste la méthode getId() et setId().
     * Vérifie que l'ID est correctement récupéré et peut être défini sur l'entité.
     */
    public function testGetSetId()
    {
        $reflection = new \ReflectionClass(Media::class);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);

        $idProperty->setValue($this->media, 1); 
        $this->assertEquals(1, $this->media->getId());
    }

    /**
     * Teste la méthode getUser() et setUser().
     * Vérifie que l'utilisateur associé à l'entité Media peut être défini et récupéré correctement.
     */
    public function testGetSetUser()
    {
        $user = new User(); 
        $this->media->setUser($user);

        $this->assertSame($user, $this->media->getUser()); 
    }

    /**
     * Teste la méthode getAlbum() et setAlbum().
     * Vérifie que l'album associé à l'entité Media peut être défini et récupéré correctement.
     */
    public function testGetSetAlbum()
    {
        $album = new Album(); 
        $this->media->setAlbum($album); 
        $this->assertSame($album, $this->media->getAlbum()); 
    }

    /**
     * Teste la méthode getPath() et setPath().
     * Vérifie que le chemin du fichier peut être défini et récupéré correctement.
     */
    public function testGetSetPath()
    {
        $path = 'path/to/media/file.jpg'; 
        $this->media->setPath($path);

        $this->assertEquals($path, $this->media->getPath()); 
    }

    /**
     * Teste la méthode getTitle() et setTitle().
     * Vérifie que le titre du fichier peut être défini et récupéré correctement.
     */
    public function testGetSetTitle()
    {
        $title = 'Media title'; 
        $this->media->setTitle($title);

        $this->assertEquals($title, $this->media->getTitle()); 
    }

    /**
     * Teste la méthode getFile() et setFile().
     * Vérifie que le fichier (UploadedFile) associé à l'entité peut être défini et récupéré correctement.
     */
    public function testGetSetFile()
    {
        // Crée un fichier temporaire pour simuler un fichier téléchargé
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_file_') . '.jpg';
        file_put_contents($tmpFile, 'Dummy content');

        // Créer un objet UploadedFile avec le fichier temporaire
        $file = new UploadedFile($tmpFile, 'testfile.jpg');

        $this->media->setFile($file);

        $this->assertSame($file, $this->media->getFile());

        // Nettoie le fichier temporaire après le test
        unlink($tmpFile);
    }

    
    /**
     * Teste la méthode setFile() avec une valeur nulle.
     * Vérifie que le fichier peut être défini à null et que cette valeur est correctement récupérée.
     */
    public function testSetFileNull()
    {
        $this->media->setFile(null);
        $this->assertNull($this->media->getFile()); 
    }
}
