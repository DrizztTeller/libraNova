<?php

namespace App\Controller;

use App\Entity\Novel;
use App\Entity\RentingHistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/romans', name: 'novel_')]
class NovelController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $repository = $entityManager->getRepository(Novel::class);
        $novels = $repository->findAll();

        return $this->json($novels);
    }

    #[Route('/{ref}', name: ' Novel', methods: ['GET'])]
    public function getNovel(Novel $novel): JsonResponse
    {
        return $this->json($novel);
    }

    #[Route('/borrow/{ref}', name: 'borrow', methods: ['POST'])]
    public function borrow(Novel $novel, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Vous devez être connecté pour emprunter un livre.'], 403);
        }

        $borrowedBooks = $entityManager->getRepository(RentingHistory::class)->count(['user' => $user, 'end' => null]);
        if ($borrowedBooks >= 5) {
            return $this->json(['error' => 'Vous ne pouvez pas emprunter plus de 5 livres.'], 400);
        }

        $existingRental = $entityManager->getRepository(RentingHistory::class)->findOneBy(['novel' => $novel, 'end' => null]);
        if ($existingRental) {
            return $this->json(['error' => 'Ce livre est déjà emprunté.'], 400);
        }

        $rental = new RentingHistory();
        $rental->setUser($user);
        $rental->setNovel($novel);
        $rental->setStart(new \DateTimeImmutable());
        $rental->setEnd(new \DateTimeImmutable('+5 days'));

        $entityManager->persist($rental);
        $entityManager->flush();

        return $this->json(['message' => 'Livre emprunté avec succès !']);
    }

    #[Route('/return/{ref}', name: 'return', methods: ['POST'])]
    public function returnBook(Novel $novel, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Vous devez être connecté.'], 403);
        }

        $rental = $entityManager->getRepository(RentingHistory::class)->findOneBy(['novel' => $novel, 'user' => $user, 'end' => null]);

        if (!$rental) {
            return $this->json(['error' => 'Ce livre n’est pas en votre possession.'], 400);
        }

        $rental->setEnd(new \DateTimeImmutable());
        $entityManager->flush();

        return $this->json(['message' => 'Livre retourné avec succès !']);
    }

    #[Route('/like/{ref}', name: 'like', methods: ['POST'])]
    public function like(Novel $novel, Security $security, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $security->getUser();
        if (!$user) {
            return $this->json(['error' => 'Vous devez être connecté pour liker un livre.'], 403);
        }

        // Vérifier si l'utilisateur a déjà liké
        if ($novel->getUsersLiked()->contains($user)) {
            return $this->json(['error' => 'Vous avez déjà liké ce livre.'], 400);
        }

        // Ajouter le like
        $novel->addLike($user);
        $entityManager->flush();

        return $this->json(['message' => 'Livre liké avec succès !', 'likes' => $novel->getUsersLiked()->count()]);
    }

    #[Route('/unlike/{ref}', name: 'unlike', methods: ['POST'])]
    public function unlike(Novel $novel, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Vous devez être connecté pour annuler un like.'], 403);
        }

        // Vérifier si l'utilisateur a déjà liké
        if (!$novel->getUsersLiked()->contains($user)) {
            return $this->json(['error' => 'Vous n’avez pas liké ce livre.'], 400);
        }

        // Supprimer le like
        $novel->removeLike($user);
        $entityManager->flush();

        return $this->json(['message' => 'Like annulé avec succès !', 'likes' => $novel->getUsersLiked()->count()]);
    }

    #[Route('/dislike/{ref}', name: 'dislike', methods: ['POST'])]
    public function dislike(Novel $novel, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Vous devez être connecté pour disliker un livre.'], 403);
        }

        // Vérifier si l'utilisateur a déjà disliké
        if ($novel->getUsersDisliked()->contains($user)) {
            return $this->json(['error' => 'Vous avez déjà disliké ce livre.'], 400);
        }

        // Ajouter le dislike
        $novel->addUserDisliked($user);
        $entityManager->flush();

        return $this->json(['message' => 'Livre disliké avec succès !', 'dislikes' => $novel->getUsersDisliked()->count()]);
    }

    #[Route('/undislike/{ref}', name: 'undislike', methods: ['POST'])]
    public function undislike(Novel $novel, Security $security, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $security->getUser();
        if (!$user) {
            return $this->json(['error' => 'Vous devez être connecté pour annuler un dislike.'], 403);
        }

        // Vérifier si l'utilisateur a déjà disliké
        if (!$novel->getUsersDisliked()->contains($user)) {
            return $this->json(['error' => 'Vous n’avez pas disliké ce livre.'], 400);
        }

        // Supprimer le dislike
        $novel->removeUserDisliked($user);
        $entityManager->flush();

        return $this->json(['message' => 'Dislike annulé avec succès !', 'dislikes' => $novel->getUsersDisliked()->count()]);
    }
}
