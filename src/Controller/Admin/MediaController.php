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
        // Vérifie si l'utilisateur a le rôle ROLE_ADMIN
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }
    
        // Logique existante pour les admins
        $page = $request->query->getInt('page', 1);
        $criteria = [];
    
        $mediaRepository = $this->entityManager->getRepository(Media::class);
        $medias = $mediaRepository->findBy(
            $criteria,
            ['id' => 'ASC'],
            25, // Limit per page
            25 * ($page - 1) // Offset
        );
    
        $total = $mediaRepository->count($criteria);
    
        return $this->render('admin/media/index.html.twig', [
            'medias' => $medias,
            'total' => $total,
            'page' => $page
        ]);
    }

    #[Route('/admin/media/add', name: 'admin_media_add')]
    public function add(Request $request): Response
    {
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
                $path = 'uploads/' . md5(uniqid()) . '.' . $file->guessExtension();
                $media->setPath($path);
                $file->move('uploads/', $path);
            }

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
