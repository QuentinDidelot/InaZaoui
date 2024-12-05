<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/', name: 'home')]
    public function home(): Response // Correction du type de retour manquant
    {
        return $this->render('front/home.html.twig');
    }

    #[Route('/guests', name: 'guests')]
    public function guests(): Response
    {
        // Récupération des invités non restreints
        $guests = $this->entityManager->getRepository(User::class)->findBy([
            'admin' => false,
            'restricted' => false,
        ]);

        return $this->render('front/guests.html.twig', [
            'guests' => $guests,
        ]);
    }

    #[Route('/guest/{id}', name: 'guest')]
    public function guest(int $id): Response
    {
        $guest = $this->entityManager->getRepository(User::class)->find($id);

        // Si l'invité est introuvable ou restreint, rediriger vers la page des invités
        if (!$guest || $guest->isRestricted()) {
            return $this->redirectToRoute('guests');
        }

        return $this->render('front/guest.html.twig', [
            'guest' => $guest,
        ]);
    }

    #[Route('/portfolio/{id}', name: 'portfolio')]
    public function portfolio(?int $id = null): Response
    {
        /** @var MediaRepository $mediaRepo */ // Annotation pour PHPStan
        $mediaRepo = $this->entityManager->getRepository(Media::class);
        $albums = $this->entityManager->getRepository(Album::class)->findAll();
        $album = $id ? $this->entityManager->getRepository(Album::class)->find($id) : null;

        // Récupération des médias non restreints
        $medias = $album 
            ? $mediaRepo->findAllMediasNotRestrictedByAlbum($album)
            : $mediaRepo->findAllMediasNotRestricted();

        return $this->render('front/portfolio.html.twig', [
            'albums' => $albums,
            'album' => $album,
            'medias' => $medias,
        ]);
    }

    #[Route('/about', name: 'about')]
    public function about(): Response // Correction du type de retour manquant
    {
        return $this->render('front/about.html.twig');
    }
}
