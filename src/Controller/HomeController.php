<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->render('front/home.html.twig');
    }

    #[Route('/guests', name: 'guests')]
    public function guests(Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 5; // Nombre de résultats par page
        $offset = ($page - 1) * $limit;

        $query = $this->entityManager->createQuery(
            'SELECT g.id, g.username, COUNT(m.id) AS mediaCount
             FROM App\Entity\User g
             LEFT JOIN g.medias m
             WHERE g.admin = false AND g.restricted = false
             GROUP BY g.id
             ORDER BY g.username ASC'
        );

        // Ajouter pagination
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        $guests = $query->getArrayResult();

        // Compter le nombre total d'invités pour calculer les pages
        $totalGuests = (int) $this->entityManager->createQuery(
            'SELECT COUNT(DISTINCT g.id) FROM App\Entity\User g WHERE g.admin = false AND g.restricted = false'
        )->getSingleScalarResult();

        $totalPages = (int) ceil($totalGuests / $limit);

        return $this->render('front/guests.html.twig', [
            'guests' => $guests,
            'currentPage' => $page,
            'totalPages' => $totalPages,
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
    public function portfolio(Request $request, ?int $id = null): Response
    {
        /** @var MediaRepository $mediaRepo */
        $mediaRepo = $this->entityManager->getRepository(Media::class);
        $albums = $this->entityManager->getRepository(Album::class)->findAll();
        $album = $id ? $this->entityManager->getRepository(Album::class)->find($id) : null;

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 9;
        $offset = ($page - 1) * $limit;

        $medias = $album
            ? $mediaRepo->findAllMediasNotRestrictedByAlbumWithPagination($album, $limit, $offset)
            : $mediaRepo->findAllMediasNotRestrictedWithPagination($limit, $offset);

        // Total pour calculer les pages
        $totalMedias = $album
            ? (int) $mediaRepo->countMediasByAlbum($album)
            : (int) $mediaRepo->countAllMedias();

        $totalPages = (int) ceil($totalMedias / $limit);

        return $this->render('front/portfolio.html.twig', [
            'albums' => $albums,
            'album' => $album,
            'medias' => $medias,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('front/about.html.twig');
    }
}
