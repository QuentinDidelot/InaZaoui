<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\GuestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    /**
     * Permet d'afficher la page des invités (non bloqués)
     */
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

    /**
     * Permet d'ajouter un nouvel invité
     */
    #[Route('/admin/guest/add', name: 'admin_guest_add')]
    public function add(Request $request): Response
    {
        $guest = new User();
        $form = $this->createForm(GuestType::class, $guest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $guest->getPassword();

            // Vérifier que le mot de passe n'est pas null ou vide avant de le hacher
            if ($plainPassword !== null && $plainPassword !== '') {
                $password = $this->userPasswordHasher->hashPassword($guest, $plainPassword);
                $guest->setPassword($password);
            }

            $guest->setRoles(['ROLE_USER']);
            $guest->setAdmin(false);
            $guest->setRestricted(false);

            $this->entityManager->persist($guest);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_guest_index');
        }

        return $this->render('admin/guest/add.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Permet de bloquer l'accès d'un invité
     */
    #[Route('/admin/guest/block/{id}', name: 'admin_guest_block')]
    public function blockAccess(User $user): RedirectResponse
    {
        $user->setRestricted(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_guest_index');
    }

    /**
     * Permet de débloquer l'accès d'un invité
     */
    #[Route('/admin/guest/unblock/{id}', name: 'admin_guest_unblock')]
    public function unblockAccess(User $user): RedirectResponse
    {
        $user->setRestricted(false);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_guest_index');
    }

    /**
     * Permet de supprimer un invité
     */
    #[Route('/admin/guest/delete/{id}', name: 'admin_guest_delete', methods:'DELETE')]
    public function delete(User $user): RedirectResponse {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        return $this->redirectToRoute('admin_guest_index');
    }

}
