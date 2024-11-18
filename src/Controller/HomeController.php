<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/', name: 'home')]
    public function home()
    {
        return $this->render('front/home.html.twig');
    }

    #[Route('/guests', name: 'guests')]
    public function guests()
    {
        // Injection du repository directement via l'EntityManager
        $guests = $this->entityManager->getRepository(User::class)->findBy(['admin' => false]);
        return $this->render('front/guests.html.twig', [
            'guests' => $guests
        ]);
    }

    #[Route('/guest/{id}', name: 'guest')]
    public function guest(int $id)
    {
        // Recherche du guest via le repository
        $guest = $this->entityManager->getRepository(User::class)->find($id);
        
        if (!$guest) {
            throw $this->createNotFoundException('Guest not found');
        }

        return $this->render('front/guest.html.twig', [
            'guest' => $guest
        ]);
    }

    #[Route('/portfolio/{id}', name: 'portfolio')]
    public function portfolio(?int $id = null)
    {
        // Récupération des albums, et du média en fonction de l'album ou de l'utilisateur
        $albums = $this->entityManager->getRepository(Album::class)->findAll();
        $album = $id ? $this->entityManager->getRepository(Album::class)->find($id) : null;
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['admin' => true]);

        $medias = $album
            ? $this->entityManager->getRepository(Media::class)->findBy(['album' => $album])
            : $this->entityManager->getRepository(Media::class)->findBy(['user' => $user]);

        return $this->render('front/portfolio.html.twig', [
            'albums' => $albums,
            'album' => $album,
            'medias' => $medias
        ]);
    }

    #[Route('/about', name: 'about')]
    public function about()
    {
        return $this->render('front/about.html.twig');
    }
}
