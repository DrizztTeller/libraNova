<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\BookmarkedFilterType;
use App\Repository\LoginHistoryRepository;
use App\Repository\BookRepository;
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

        // TODO faire le template
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
    public function bookmarked(BookRepository $nr, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'Veuillez vous connecter pour voir vos favoris.');
            return $this->redirectToRoute('home');
        }

        // TODO faire une seule fonction de recherche complète avec un seul form qui sera réutilisé partout pour filtrer directement dans le tableau des livres (all livres ou que livres liké ou que livres empruntés)

        $form = $this->createForm(BookmarkedFilterType::class);
        $form->handleRequest($request);

        $filterCriteria = [];
        $sortCriteria = ['title' => 'DESC'];

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Filtres
            switch ($data['publication_status']) {
                case 'published':
                    $filterCriteria['is_published'] = true;
                    break;
                case 'unpublished':
                    $filterCriteria['is_published'] = false;
                    break;
                case 'newly_available':
                    $filterCriteria['is_published'] = true;
                    $filterCriteria['newly_available'] = true;
                    break;
            }

            if (!empty($data['tags'])) {
                $filterCriteria['tags'] = $data['tags'];
            }

            // Tri
            $sortField = $data['sort_by'] ?? 'title';
            $sortOrder = $data['sort_order'] ?? 'DESC';
            $sortCriteria = [$sortField => $sortOrder];
        }

        $books = $nr->findBookmarkedWithFilters(
            $this->getUser(),
            $filterCriteria,
            $sortCriteria
        );

        return $this->render('user/bookmarked.html.twig', [
            'form' => $form->createView(),
            'books' => $books
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

        // TODO faire une seule fonction de recherche complète avec un seul form qui sera réutilisé partout pour filtrer directement dans le tableau des s (all livres ou que livres liké ou que livres empruntés)

        $currentRentals = $rhr->findCurrentRentalsForUser($user);

        // dd($currentRentals);
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

        // TODO faire une seule fonction de recherche complète avec un seul form qui sera réutilisé partout pour filtrer directement dans le tableau des livres (all livres ou que livres liké ou que livres empruntés)

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

        $allLogins = $lhr->findBy(['user' => $user]);

        return $this->render('user/logins_history.html.twig', [
            'user' => $user,
            'allLogins' => $allLogins
        ]);
    }
}
