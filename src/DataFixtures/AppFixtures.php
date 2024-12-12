<?php

namespace App\DataFixtures;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $userPasswordHasher;
    private string $projectDir;

    // Injection des services nécessaires via le constructeur
    public function __construct(UserPasswordHasherInterface $userPasswordHasher, ParameterBagInterface $params)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    
        // Récupère le paramètre 'kernel.project_dir'
        $projectDir = $params->get('kernel.project_dir');
    
        // Vérifie si le paramètre est une chaîne valide, sinon lance une exception
        if (!is_string($projectDir) || empty($projectDir)) {
            throw new \RuntimeException('The project directory parameter is not a valid string.');
        }
    
        // Assigne la valeur validée à la propriété $projectDir
        $this->projectDir = $projectDir;
    }
    
    

    public function load(ObjectManager $manager): void
    {
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

        // Chemin du répertoire des images optimisées
        $imagesDirectory = $this->projectDir . '/public/uploadsResized/nature/';
        $imageFiles = (is_array($scannedImages = scandir($imagesDirectory)) ? array_diff($scannedImages, ['.', '..']) : []);

        // Vérification si des images existent
        if (empty($imageFiles)) {
            throw new \Exception("Aucune image trouvée dans le répertoire : $imagesDirectory");
        }

        sort($imageFiles);
        
        // Distribution des images entre utilisateurs
        $totalImages = count($imageFiles);
        $imagesPerUser = intdiv($totalImages, count($users));
        $extraImages = $totalImages % count($users);

        $imageIndex = 0;
        foreach ($users as $user) {
            $userImageCount = $imagesPerUser + ($extraImages > 0 ? 1 : 0);
            $extraImages--;

            for ($i = 0; $i < $userImageCount && $imageIndex < $totalImages; $i++) {
                $imageName = isset($imageFiles[$imageIndex]) ? $imageFiles[$imageIndex] : null;
                $imagePath = "/uploadsResized/nature/" . $imageName;

                $media = new Media();
                $media->setPath($imagePath);
                $media->setTitle("Image Nature $imageIndex");
                $media->setAlbum($albumNature);
                $media->setUser($user);

                $manager->persist($media);
                $imageIndex++;
            }
        }

        // Flushing pour enregistrer les modifications
        $manager->flush();
    }
}
