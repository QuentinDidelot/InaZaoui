<?php

namespace App\DataFixtures;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Création d'un utilisateur ROLE_ADMIN
        $adminUser = new User();
        $adminUser->setUserName('ina');
        $adminUser->setEmail('ina@mail.com');
        $adminUser->setDescription('Description utilisateur Ina');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setAdmin(true);
        $adminUser->setPassword($this->userPasswordHasher->hashPassword($adminUser, 'password'));
        $manager->persist($adminUser);

        // Création d'albums
        $albumAmerica = new Album();
        $albumAmerica->setName('Amérique');
        $manager->persist($albumAmerica);

        $albumEurope = new Album();
        $albumEurope->setName('Europe');
        $manager->persist($albumEurope);

        $albumAsia = new Album();
        $albumAsia->setName('Asie');
        $manager->persist($albumAsia);

        $albumAfrica = new Album();
        $albumAfrica->setName('Afrique');
        $manager->persist($albumAfrica);

        // Création de médias pour les albums
        $americaMediaPath = [
            "uploads/usa-1.jpg",
            "uploads/usa-2.jpg",
            "uploads/usa-3.jpg"
        ];

        foreach ($americaMediaPath as $index => $path) {
            $media = new Media();
            $media->setPath($path);
            $media->setTitle('Amerique'. ($index +1));
            $media->setAlbum($albumAmerica);
            $media->setUser($adminUser);
            $manager->persist($media);
        }

        $europeMediaPath = [
            "uploads/uk-1.jpg",
            "uploads/uk-2.jpg",
            "uploads/uk-3.jpg"
        ];


        foreach ($europeMediaPath as $index => $path) {
            $media = new Media();
            $media->setPath($path);
            $media->setTitle('Europe'. ($index +1));
            $media->setAlbum($albumEurope);
            $media->setUser($adminUser);
            $manager->persist($media);
        }

        $asiaMediaPath = [
            "uploads/jp-1.jpg",
            "uploads/jp-2.jpg",
            "uploads/jp-3.jpg"
        ];

        foreach ($asiaMediaPath as $index => $path) {
            $media = new Media();
            $media->setPath($path);
            $media->setTitle('Asia'. ($index +1));
            $media->setAlbum($albumAsia);
            $media->setUser($adminUser);
            $manager->persist($media);
        }


        $africaMediaPath = [
            "uploads/af-1.jpg",
            "uploads/af-2.jpg",
            "uploads/af-3.jpg"
        ];

        foreach ($africaMediaPath as $index => $path) {
            $media = new Media();
            $media->setPath($path);
            $media->setTitle('Afrique'. ($index +1));
            $media->setAlbum($albumAfrica);
            $media->setUser($adminUser);
            $manager->persist($media);
        }

        $manager->flush();
    }

}
