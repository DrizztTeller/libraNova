<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profil', name: 'app_user_')]
final class UserController extends AbstractController{

    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/', name: 'profile', methods: ['GET'])]
    public function profile(User $user): Response
    {
        if (!$user->isVerified()) {
            $this->addFlash('warning', 'Validez votre email !');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{ref}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getRef(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }
        $this->addFlash('success', 'Votre compte a bien été supprimé !');
        return $this->redirectToRoute('app_user_profile', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/favoris', name: 'bookmarked', methods: ['GET', 'POST'])]
    public function bookmarked(UserRepository $userRepository, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            $this->addFlash('warning', 'Vous devez être connecté pour voir vos favoris');
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }

        if (!$user->isVerified()) {
            $this->addFlash('warning', 'Votre email doit être validé pour accéder à vos favoris.');
            return $this->redirectToRoute('app_user_profile', [], Response::HTTP_SEE_OTHER);
        }

        // $filters = [];

        // Créer le formulaire de filtre
        // $form = $this->createForm(FiltersLikesEntitiesType::class);
        // $form->handleRequest($request);

        // Si le formulaire est soumis et valide, récupérer les données des filtres
        // if ($form->isSubmitted() && $form->isValid()) {
        //     $filters = $form->getData();
        // }

        // Récupérer les entités bookmarked selon les filtres
        // $bookmarked = $userRepository->getBookmarked($user, $filters, $filters['orderBy'] ?? null, $filters['orderDirection'] ?? 'ASC');

        return $this->render('user/bookmarked.html.twig', [
            'user' => $user,
            // 'form' => $form,
            'novels' => $bookmarked['novels'] ?? [],
            
        ]);
    }
}
