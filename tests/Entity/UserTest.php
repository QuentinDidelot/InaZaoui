<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Media;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\ArrayCollection;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        // Initialiser un nouvel objet User avant chaque test
        $this->user = new User();
    }

    public function testGetId(): void
    {
        $refObject = new \ReflectionObject($this->user);
        $idProperty = $refObject->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->user, 123);
        $this->assertSame(123, $this->user->getId());
    }

    public function testGetSetEmail(): void
    {
        // Teste la méthode getEmail() et setEmail()
        $this->user->setEmail('john@me.com');
        $this->assertSame('john@me.com', $this->user->getEmail());
    }

    public function testGetSetUsername(): void
    {
        // Teste la méthode getUsername() et setUsername()
        $this->user->setUsername('john');
        $this->assertSame('john', $this->user->getUsername());
    }

    public function testGetSetDescription(): void
    {
        // Teste la méthode getDescription() et setDescription()
        $this->user->setDescription('Ceci est un test');
        $this->assertSame('Ceci est un test', $this->user->getDescription());
    }

    public function testSetMedias(): void
    {
        // Crée une nouvelle collection de médias
        $media1 = new Media();
        $media2 = new Media();
        $medias = new ArrayCollection([$media1, $media2]);

        // Définit la collection de médias via setMedias()
        $this->user->setMedias($medias);

        // Vérifie que la collection de médias de l'utilisateur est mise à jour
        $this->assertCount(2, $this->user->getMedias());
        $this->assertSame($media1, $this->user->getMedias()->first());
        $this->assertSame($media2, $this->user->getMedias()->last());
    }

    public function testAddMedia(): void
    {
        // Teste la méthode addMedia()
        $media = new Media();
        $this->user->addMedia($media);

        // Vérifie que le média a bien été ajouté à la collection
        $this->assertCount(1, $this->user->getMedias());
        $this->assertSame($media, $this->user->getMedias()->first());
    }

    public function testRemoveMedia(): void
    {
        // Teste la méthode removeMedia()
        $media = new Media();
        $this->user->addMedia($media);

        // Vérifie que le média est bien ajouté
        $this->assertCount(1, $this->user->getMedias());

        // Retirer le média
        $this->user->removeMedia($media);

        // Vérifie que le média a bien été supprimé
        $this->assertCount(0, $this->user->getMedias());
    }

    public function testGetSetPassword(): void
    {
        // Teste la méthode getPassword() et setPassword()
        $this->user->setPassword('john');
        $this->assertSame('john', $this->user->getPassword());
    }

    public function testGetRoles(): void
    {
        // Teste la méthode getRoles()
        $this->user->setRoles(['ROLE_USER']);
        $roles = $this->user->getRoles();
        $this->assertContains('ROLE_USER', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testIsAdmin(): void
    {
        // Teste la méthode isAdmin() et setAdmin()
        $this->user->setAdmin(true);
        $this->assertTrue($this->user->isAdmin());

        $this->user->setAdmin(false);
        $this->assertFalse($this->user->isAdmin());
    }

    public function testIsRestricted(): void
    {
        // Teste la méthode isRestricted() et setRestricted()
        $this->user->setRestricted(true);
        $this->assertTrue($this->user->isRestricted());

        $this->user->setRestricted(false);
        $this->assertFalse($this->user->isRestricted());
    }

    public function testGetUserIdentifier(): void
    {
        // Teste la méthode getUserIdentifier()
        $this->user->setUsername('john_sheppard');
        $this->assertSame('john_sheppard', $this->user->getUserIdentifier());
    }

    public function testEraseCredentials(): void
    {
        // Teste la méthode eraseCredentials()
        $this->user->eraseCredentials();
        $this->assertNull($this->user->getPassword());
    }

    public function testGetSalt(): void
    {
        // Teste la méthode getSalt(), qui est généralement inutile avec bcrypt ou argon2
        $this->assertNull($this->user->getSalt());
    }
}
