<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\GuestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/admin/guest/add', name: 'admin_guest_add')]
    public function add(Request $request): Response {
        $guest = new User();
        $form = $this->createForm(GuestType::class, $guest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $this->userPasswordHasher->hashPassword($guest, $guest->getPassword());
            $guest->setPassword($password);
            $guest->setRoles(['ROLE_USER']);
            $guest->setAdmin(false);
            $guest->setRestricted(false);

            $this->entityManager->persist($guest);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_guest_index');
        }
    return $this->render('admin/guest/add.html.twig', ['form' => $form->createView()]);
    }

}
