<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Form\MediaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\User;
use App\Service\ResizerService;

#[IsGranted('ROLE_USER')]
class MediaController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private Filesystem $filesystem;

    public function __construct(EntityManagerInterface $entityManager, Filesystem $filesystem)
    {
        $this->entityManager = $entityManager;
        $this->filesystem = $filesystem;
    }

    #[Route('/admin/media', name: 'admin_media_index')]
    public function index(Request $request): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }
    
        // Pagination et limite
        $page = max($request->query->getInt('page', 1), 1);
        $limit = 25;
    
        // Récupération des données
        $mediaRepository = $this->entityManager->getRepository(Media::class);
        $total = $mediaRepository->count([]);
    
        // Calcul du total de pages
        $totalPages = (int)ceil($total / $limit);
        
        // Redirection si la page demandée dépasse le total
        if ($page > $totalPages && $totalPages > 0) {
            return $this->redirectToRoute('admin_media_index', ['page' => $totalPages]);
        }
    
        // Récupération des médias pour la page actuelle
        $medias = $mediaRepository->createQueryBuilder('m')
            ->orderBy('m.id', 'ASC')
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    
        // Passage au template
        return $this->render('admin/media/index.html.twig', [
            'medias' => $medias,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => $totalPages,
        ]);
    }
    

    #[Route('/admin/media/add', name: 'admin_media_add')]
    public function add(Request $request): Response
    {
        $resizer = new ResizerService();
    
        $media = new Media();
        $form = $this->createForm(MediaType::class, $media, ['is_admin' => $this->isGranted('ROLE_ADMIN')]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                // Casting explicite du UserInterface à l'entité User
                $media->setUser($this->getUser() instanceof User ? $this->getUser() : null);
            }
    
            /** @var UploadedFile|null $file */
            $file = $media->getFile();
    
            if ($file) {
                // On récupère le titre de l'image, et on l'utilise comme base pour le nom du fichier
                $title = $media->getTitle(); // On suppose que 'getTitle' est un champ du formulaire
    
                // Génération du nom du fichier
                $extension = $file->guessExtension(); // Extension de l'image (ex: jpg, png, etc.)
                $baseName = $title . '.' . $extension;  // Nom de base de l'image
    
                // Vérification si le fichier existe déjà
                $path = $this->getParameter('uploads') . DIRECTORY_SEPARATOR . 'nature';
                $newFileName = $baseName;
                $counter = 1;
    
                // Si le fichier existe déjà, on ajoute un suffixe (1), (2), etc.
                while (file_exists($path . DIRECTORY_SEPARATOR . $newFileName)) {
                    $newFileName = $title . '(' . $counter . ').' . $extension;
                    $counter++;
                }
    
                // Sauvegarde de l'image avec le nouveau nom
                $file->move($path, $newFileName);
    
                // Maintenant, on redimensionne l'image
                $resizedImage = $resizer->resize($newFileName, $path, 'uploadsResized' . DIRECTORY_SEPARATOR . 'nature');
                $media->setPath(ltrim($resizedImage, DIRECTORY_SEPARATOR));
            }
    
            // Enregistrement du média dans la base de données
            $this->entityManager->persist($media);
            $this->entityManager->flush();
    
            return $this->redirectToRoute('admin_media_index');
        }
    
        return $this->render('admin/media/add.html.twig', ['form' => $form->createView()]);
    }
    

    #[Route('/admin/media/delete/{id}', name: 'admin_media_delete')]
    public function delete(int $id): Response
    {
        $media = $this->entityManager->getRepository(Media::class)->find($id);

        if (!$media) {
            throw $this->createNotFoundException('The media does not exist');
        }

        // Delete the media file from the filesystem
        $filePath = $media->getPath();
        if ($this->filesystem->exists($filePath)) {
            $this->filesystem->remove($filePath);
        }

        $this->entityManager->remove($media);
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_media_index');
    }
}
