<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\BookmarkedFilterType;
use App\Repository\LoginHistoryRepository;
use App\Repository\NovelRepository;
use App\Repository\RentingHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/profil', name: 'app_user_')]
final class UserController extends AbstractController
{

    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/', name: 'profile', methods: ['GET'])]
    public function profile(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'Veuillez vous connecter pour voir vos favoris.');
            return $this->redirectToRoute('home');
        }

        if (!$user->isVerified()) {
            $this->addFlash('warning', 'Merci de validez votre email');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{ref}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getRef(), $request->getPayload()->getString('_token'))) {
            $this->em->remove($user);
            $this->em->flush();
        }
        $this->addFlash('success', 'Votre compte a bien été supprimé !');
        return $this->redirectToRoute('app_user_profile', [], Response::HTTP_SEE_OTHER);
    }

    #[IsGranted('ROLE_VERIFIED')]
    #[Route('/favoris', name: 'bookmarked', methods: ['GET', 'POST'])]
    public function bookmarked(NovelRepository $nr, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'Veuillez vous connecter pour voir vos favoris.');
            return $this->redirectToRoute('home');
        }

        if (!$user->isVerified()) {
            $this->addFlash('danger', 'Votre email doit être validé pour accéder à vos favoris.');
            return $this->redirectToRoute('app_user_profile');
        }

        $form = $this->createForm(BookmarkedFilterType::class);
        $form->handleRequest($request);

        $filterCriteria = [];
        $sortCriteria = ['title' => 'DESC'];

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Filtres
            $filterCriteria = [
                'is_published' => $data['is_published'] ?? false,
                'newly_available' => $data['newly_available'] ?? false,
                'tags' => $data['tags'] ? $data['tags']->toArray() : []
            ];

            // Tri
            $sortField = $data['sort_by'];
            $sortOrder = $data['sort_order'] ?? 'DESC'; 

            if ($sortField === 'popularity') {
                $sortCriteria = ['likes_count' => $sortOrder];
            } else {
                $sortCriteria = [$sortField => $sortOrder];
            }
        }

        $novels = $nr->findBookmarkedWithFilters(
            $user,
            $filterCriteria,
            $sortCriteria
        );

        return $this->render('user/bookmarked.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'novels' => $novels
        ]);
    }

    #[IsGranted('ROLE_VERIFIED')]
    #[Route('/emprunts', name: 'rented', methods: ['GET', 'POST'])]
    public function rented(RentingHistoryRepository $rhr): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'Veuillez vous connecter pour voir vos emprunts.');
            return $this->redirectToRoute('home');
        }

        //Inutile car pas possible d'emprunter si email non vérifié
        if (!$user->isVerified()) {
            $this->addFlash('danger', 'Votre email doit être validé pour voir vos emprunts');
            return $this->redirectToRoute('app_user_profile');
        }

        $currentRentals = $rhr->findCurrentRentalsForUser($user);

        return $this->render('user/renting_list.html.twig', [
            'user' => $user,
            'rentings' => $currentRentals
        ]);
    }

    #[IsGranted('ROLE_VERIFIED')]
    #[Route('/historique-emprunts', name: 'renting_history', methods: ['GET', 'POST'])]
    public function renting_history(RentingHistoryRepository $rhr): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', "Veuillez vous connecter pour voir l'historique de vos emprunts");
            return $this->redirectToRoute('home');
        }

        if (!$user->isVerified()) {
            $this->addFlash('danger', "Votre email doit être validé pour accéder à votre historique d'emprunts");
            return $this->redirectToRoute('app_user_profile');
        }

        $allRenting = $rhr->findBy(['user' => $user]);

        return $this->render('user/renting_history.html.twig', [
            'user' => $user,
            'allRenting' => $allRenting
        ]);
    }

    #[IsGranted('ROLE_VERIFIED')]
    #[Route('/historique-connexion', name: 'login_history', methods: ['GET', 'POST'])]
    public function login_history(LoginHistoryRepository $lhr): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'Veuillez vous connecter pour voir votre historique de connexion.');
            return $this->redirectToRoute('home');
        }

        if (!$user->isVerified()) {
            $this->addFlash('danger', 'Votre email doit être validé pour accéder à votre historique de connexion.');
            return $this->redirectToRoute('app_user_profile');
        }

        $allLogins = $lhr->findBy(['user' => $user]);

        return $this->render('user/logins_history.html.twig', [
            'user' => $user,
            'allLogins' => $allLogins
        ]);
    }
}
