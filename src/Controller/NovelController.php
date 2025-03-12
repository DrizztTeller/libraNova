<?php

namespace App\Controller;

use App\Entity\Novel;
use App\Entity\RentingHistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/api/novels', name: 'novel_')]
class NovelController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $repository = $entityManager->getRepository(Novel::class);
        $novels = $repository->findAll();

        return $this->json($novels);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getNovel(Novel $novel): JsonResponse
    {
        return $this->json($novel);
    }

    #[Route('/borrow/{id}', methods: ['POST'])]
    public function borrow(Novel $novel, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Vous devez être connecté pour emprunter un livre.'], 403);
        }

        // Vérifier si l'utilisateur n'a pas déjà emprunté 5 livres
        $borrowedBooks = $entityManager->getRepository(RentingHistory::class)->count(['user' => $user, 'end' => null]);
        if ($borrowedBooks >= 5) {
            return $this->json(['error' => 'Vous ne pouvez pas emprunter plus de 5 livres.'], 400);
        }

        // Vérifier si le livre est déjà emprunté
        $existingRental = $entityManager->getRepository(RentingHistory::class)->findOneBy(['novel' => $novel, 'end' => null]);
        if ($existingRental) {
            return $this->json(['error' => 'Ce livre est déjà emprunté.'], 400);
        }

        // Enregistrer l'emprunt
        $rental = new RentingHistory();
        $rental->setUser($user);
        $rental->setNovel($novel);
        $rental->setStart(new \DateTimeImmutable());
        $rental->setEnd(null);

        $entityManager->persist($rental);
        $entityManager->flush();

        return $this->json(['message' => 'Livre emprunté avec succès !']);
    }

    #[Route('/return/{id}', methods: ['POST'])]
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

    #[Route('/like/{id}', methods: ['POST'])]
    public function like(Novel $novel, Security $security, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $security->getUser();
        if (!$user) {
            return $this->json(['error' => 'Vous devez être connecté pour liker un livre.'], 403);
        }

        $novel->setLikes($novel->getLikes() + 1);
        $entityManager->flush();

        return $this->json(['message' => 'Livre liké avec succès !', 'likes' => $novel->getLikes()]);
    }

    #[Route('/dislike/{id}', methods: ['POST'])]
    public function dislike(Novel $novel, Security $security, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $security->getUser();
        if (!$user) {
            return $this->json(['error' => 'Vous devez être connecté pour disliker un livre.'], 403);
        }

        $novel->setDislikes($novel->getDislikes() + 1);
        $entityManager->flush();

        return $this->json(['message' => 'Livre disliké avec succès !', 'dislikes' => $novel->getDislikes()]);
    }
}
