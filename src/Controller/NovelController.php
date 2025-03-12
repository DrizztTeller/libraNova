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
        $novels = $entityManager->getRepository(Novel::class)->findAll();
        return $this->json($novels);
    }

    #[Route('/{ref}', name: 'show', methods: ['GET'])]
    public function getNovel(string $ref, EntityManagerInterface $entityManager): JsonResponse
    {
        $novel = $entityManager->getRepository(Novel::class)->findOneBy(['ref' => $ref]);

        if (!$novel) {
            return $this->json(['error' => 'Roman non trouvé'], 404);
        }

        return $this->json($novel);
    }

    #[Route('/borrow/{ref}', name: 'borrow', methods: ['POST'])]
    public function borrow(string $ref, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Vous devez être connecté pour emprunter un livre.'], 403);
        }

        $novel = $entityManager->getRepository(Novel::class)->findOneBy(['ref' => $ref]);
        if (!$novel) {
            return $this->json(['error' => 'Roman non trouvé'], 404);
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
    public function returnBook(string $ref, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Vous devez être connecté.'], 403);
        }

        $novel = $entityManager->getRepository(Novel::class)->findOneBy(['ref' => $ref]);
        if (!$novel) {
            return $this->json(['error' => 'Roman non trouvé'], 404);
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
    public function like(string $ref, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Vous devez être connecté pour liker un livre.'], 403);
        }

        $novel = $entityManager->getRepository(Novel::class)->findOneBy(['ref' => $ref]);
        if (!$novel) {
            return $this->json(['error' => 'Roman non trouvé'], 404);
        }

        if ($novel->getUsersLiked()->contains($user)) {
            return $this->json(['error' => 'Vous avez déjà liké ce livre.'], 400);
        }

        $novel->addLike($user);
        $entityManager->flush();

        return $this->json(['message' => 'Livre liké avec succès !', 'likes' => $novel->getUsersLiked()->count()]);
    }

    #[Route('/unlike/{ref}', name: 'unlike', methods: ['POST'])]
    public function unlike(string $ref, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Vous devez être connecté pour annuler un like.'], 403);
        }

        $novel = $entityManager->getRepository(Novel::class)->findOneBy(['ref' => $ref]);
        if (!$novel) {
            return $this->json(['error' => 'Roman non trouvé'], 404);
        }

        if (!$novel->getUsersLiked()->contains($user)) {
            return $this->json(['error' => 'Vous n’avez pas liké ce livre.'], 400);
        }

        $novel->removeLike($user);
        $entityManager->flush();

        return $this->json(['message' => 'Like annulé avec succès !', 'likes' => $novel->getUsersLiked()->count()]);
    }
}
