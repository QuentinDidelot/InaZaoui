<?php

namespace App\DataFixtures;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Création de l'OptimizerChain pour compresser les images
        $optimizerChain = OptimizerChainFactory::create();

        // Création des utilisateurs spécifiques
        $users = [];
        
        // Admin Ina
        $adminUser = new User();
        $adminUser->setUserName('ina');
        $adminUser->setEmail('ina@gmail.com');
        $adminUser->setDescription('Administratrice Ina');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setAdmin(true);
        $adminUser->setRestricted(false); // Non restreint
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
        $guestUserJohn->setRestricted(false); // Non restreint
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
        $restrictedUser->setRestricted(true); // Restreint
        $restrictedUser->setPassword($this->userPasswordHasher->hashPassword($restrictedUser, 'password'));
        $manager->persist($restrictedUser);
        $users[] = $restrictedUser;

        // Autres utilisateurs
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
            $user->setRestricted(false);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
            $manager->persist($user);
            $users[] = $user;
        }

        // Création des albums
        $albumNature = new Album();
        $albumNature->setName('Nature');
        $manager->persist($albumNature);

        // Génération de 3000 chemins d'images dans "uploads/nature/"
        $totalImages = 3000;
        $imagesPerUser = intdiv($totalImages, count($users));
        $natureMediaPaths = [];

        for ($i = 1; $i <= $totalImages; $i++) {
            $natureMediaPaths[] = "/uploads/nature/" . str_pad($i, 4, '0', STR_PAD_LEFT) . ".jpg";
        }

        // Répartition égale des images entre les utilisateurs
        $imageIndex = 0;

        foreach ($users as $user) {
            for ($i = 0; $i < $imagesPerUser; $i++) {
                $path = $natureMediaPaths[$imageIndex];
                $this->compressImage($path, $optimizerChain);

                $media = new Media();
                $media->setPath($path);
                $media->setTitle('Nature Image ' . ($imageIndex + 1));
                $media->setAlbum($albumNature);
                $media->setUser($user);
                $manager->persist($media);

                $imageIndex++;
            }
        }

        // Flushing pour enregistrer les modifications
        $manager->flush();
    }

    private function compressImage(string $imagePath, $optimizerChain): void
    {
        if (file_exists($imagePath)) {
            $optimizerChain->optimize($imagePath);
        }
    }
}
