<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Security\EmailVerifier;
use App\Form\BookmarkedFilterType;
use App\Repository\BookRepository;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LoginHistoryRepository;
use App\Repository\RentingHistoryRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/profil', name: 'app_user_')]
final class UserController extends AbstractController
{

    public function __construct(private EntityManagerInterface $em) {}

    #[IsGranted('ROLE_USER')]
    #[Route('/', name: 'profile', methods: ['GET'])]
    public function profile(Request $request, UserPasswordHasherInterface $uphi, EmailVerifier $emailVerifier): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'Veuillez vous connecter pour voir votre profil.');
            return $this->redirectToRoute('home');
        }

        if (!$user->isVerified()) {
            $this->addFlash('warning', 'Merci de validez votre email');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedPassword = $form->get('password')->getData();
            if (!$submittedPassword) {
                $this->addFlash('error', 'Veuillez taper votre mot de passe actuel');
                return $this->redirectToRoute('app_user_profil');
            }
            $pwd = $uphi->isPasswordValid($user, $submittedPassword);
            if ($pwd) {
                if ($form->get('email')->getData() !== $user->getEmail()) {
                    $user->setEmail($form->get('email')->getData());
                    $user->setIsVerified(false);

                    $emailVerifier->sendEmailConfirmation(
                        'app_verify_email',
                        $user,
                        (new TemplatedEmail())
                            ->from(new Address('modification@libranova.com', 'LibraNova Inc'))
                            ->to((string) $user->getEmail())
                            ->subject('Merci de confirmer votre email')
                            ->htmlTemplate('registration/confirmation_email.html.twig')
                    );
                }

                $plainPassword = $form->get('plainPassword')->getData();
                if (!empty($plainPassword)) {
                    $user->setPassword($uphi->hashPassword($user, $plainPassword));
                }

                if ($form->get('username')->getData() !== $user->getUsername()) {
                    $username = $form->get('username')->getData();
                    // encode the plain password
                    $user->setUsername($username);
                }

                $this->em->persist($user);
                $this->em->flush();

                // Redirection avec flash message
                $this->addFlash('success', 'Votre profil à été mis à jour');
            } else {
                $this->addFlash('error', 'Vos identifiants sont incorrects');
            }

            return $this->redirectToRoute('app_user_profil');
        }

        return $this->render('user/profile.html.twig', [
            'userForm' => $form,
            'user' => $user,
        ]);
    }

    // #[Route('/{ref}', name: 'delete', methods: ['POST'])]
    // public function delete(Request $request, User $user): Response
    // {
    //     if ($this->isCsrfTokenValid('delete' . $user->getRef(), $request->getPayload()->getString('_token'))) {
    //         $this->em->remove($user);
    //         $this->em->flush();
    //     }
    //     $this->addFlash('success', 'Votre compte a bien été supprimé !');
    //     return $this->redirectToRoute('app_user_profile', [], Response::HTTP_SEE_OTHER);
    // }

    #[Route('/{ref}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, UserRepository $userRepository, string $ref): Response
    {
        $user = $userRepository->findOneBy(['ref' => $ref]);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

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
            'form' => $form,
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
