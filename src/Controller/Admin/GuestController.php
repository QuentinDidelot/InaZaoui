<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_ADMIN")]
class GuestController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager,
                                private readonly UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    #[Route('/admin/guest', name: 'admin_guest_index')]
    public function index(): Response
    {
        $guests = $this->entityManager->getRepository(User::class)->findBy([
            'admin' => false,
        ]);

        return $this->render('admin/guest/index.html.twig', [
            'guests' => $guests,
        ]);
    }
}
