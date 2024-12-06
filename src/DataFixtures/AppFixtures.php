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
        // Création des utilisateurs spécifiques (admin, restreint, non restreint)
        $users = [];
        
        // Admin Ina
        $adminUser = new User();
        $adminUser->setUserName('ina');
        $adminUser->setEmail('ina@gmail.com');
        $adminUser->setDescription('Administratrice Ina');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setAdmin(true);
        $adminUser->setRestricted(false);  // Non restreint
        $adminUser->setPassword($this->userPasswordHasher->hashPassword($adminUser, 'password'));
        $manager->persist($adminUser);
        $users[] = $adminUser;

        // Invité non restreint John
        $guestUserJohn = new User();
        $guestUserJohn->setUserName('john');
        $guestUserJohn->setEmail('john@gmail.com');
        $guestUserJohn->setDescription('Invité non restreint');
        $guestUserJohn->setRoles(['ROLE_USER']);
        $guestUserJohn->setAdmin(false);
        $guestUserJohn->setRestricted(false);  // Non restreint
        $guestUserJohn->setPassword($this->userPasswordHasher->hashPassword($guestUserJohn, 'password'));
        $manager->persist($guestUserJohn);
        $users[] = $guestUserJohn;

        // Invité restreint Kaidan
        $restrictedUser = new User();
        $restrictedUser->setUserName('kaidan');
        $restrictedUser->setEmail('kaidan@gmail.com');
        $restrictedUser->setDescription('Invité restreint');
        $restrictedUser->setRoles(['ROLE_USER']);
        $restrictedUser->setAdmin(false);
        $restrictedUser->setRestricted(true);  // Restreint
        $restrictedUser->setPassword($this->userPasswordHasher->hashPassword($restrictedUser, 'password'));
        $manager->persist($restrictedUser);
        $users[] = $restrictedUser;

        // Création des utilisateurs Mass Effect (Garrus, Tali, etc.)
        $massEffectNames = [
            'Garrus', 'Tali', 'Liara', 'Wrex', 'Ashley', 'Thane', 'Mordin',
            'Jack', 'Grunt', 'EDI', 'Javik', 'Cortez', 'Vega', 'Kasumi', 'Zaeed', 'Samara',
            'Edena', 'Saren', 'Rex', 'Benezia', 'Udina', 'Nihlus'
        ];

        foreach ($massEffectNames as $userName) {
            $user = new User();
            $user->setUserName($userName);
            $user->setEmail(strtolower($userName) . '@gmail.com');
            $user->setDescription("Description de $userName");
            $user->setRoles(['ROLE_USER']);
            $user->setAdmin(false);
            $user->setRestricted(false); // Non restreint
            $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
            $manager->persist($user);
            $users[] = $user;
        }

        // Création des albums
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

        $albumNature = new Album();
        $albumNature->setName('Nature');
        $manager->persist($albumNature);

        // Création de médias pour les albums Amérique, Europe, Asie, Afrique
        $americaMediaPath = [
            "uploads/usa-1.jpg",
            "uploads/usa-2.jpg",
            "uploads/usa-3.jpg"
        ];

        foreach ($americaMediaPath as $index => $path) {
            $media = new Media();
            $media->setPath($path);
            $media->setTitle('Amerique ' . ($index + 1));
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
            $media->setTitle('Europe ' . ($index + 1));
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
            $media->setTitle('Asia ' . ($index + 1));
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
            $media->setTitle('Afrique ' . ($index + 1));
            $media->setAlbum($albumAfrica);
            $media->setUser($adminUser);
            $manager->persist($media);
        }

        // Génération de 3000 chemins d'images dans "uploads/nature/"
        $natureMediaPaths = [];
        for ($i = 1; $i < 3000; $i++) {
            $natureMediaPaths[] = "uploads/nature/" . str_pad($i, 4, '0', STR_PAD_LEFT) . ".jpg";
        }

        // Associer les images "Nature" aux utilisateurs de manière circulaire
        $userCount = count($users);
        $userIndex = 0;

        foreach ($natureMediaPaths as $index => $path) {
            $user = $users[$userIndex % $userCount];  // Associer de manière circulaire si moins d'utilisateurs que d'images

            // Création du média et association avec l'utilisateur et l'album Nature
            $media = new Media();
            $media->setPath($path);
            $media->setTitle('Nature Image ' . ($index + 1));
            $media->setAlbum($albumNature);
            $media->setUser($user);
            $manager->persist($media);

            // Passer à l'utilisateur suivant pour la prochaine image
            $userIndex++;
        }

        // Flushing pour enregistrer les modifications dans la base de données
        $manager->flush();
    }
}
