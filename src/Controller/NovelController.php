<?php

namespace App\Controller;

use App\Entity\Novel;
use App\Entity\RentingHistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/novels', name: 'novel_')]
class NovelController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $repository = $entityManager->getRepository(Novel::class);
        $queryBuilder = $repository->createQueryBuilder('n');

        // 🔎 Recherche et filtres
        if ($request->query->get('author')) {
            $queryBuilder->andWhere('n.author LIKE :author')
                         ->setParameter('author', '%'.$request->query->get('author').'%');
        }
        if ($request->query->get('genre')) {
            $queryBuilder->andWhere('n.genre = :genre')
                         ->setParameter('genre', $request->query->get('genre'));
        }
        if ($request->query->get('popular')) {
            $queryBuilder->orderBy('n.likes', 'DESC');
        }

        $novels = $queryBuilder->getQuery()->getResult();

        return $this->json($novels);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getNovel(Novel $novel): JsonResponse
    {
        return $this->json($novel);
    }

    #[Route('/create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true);

        $novel = new Novel();
        $novel->setName($data['name']);
        $novel->setAuthor($data['author']);
        $novel->setAbstract($data['abstract']);
        $novel->setIsPublished($data['is_published'] ?? false);
        $novel->setReleasedAt(new \DateTime($data['released_at'] ?? 'now'));
        $novel->setSlug($data['slug'] ?? strtolower(str_replace(' ', '-', $data['name'])));
        $novel->setIsForAdult($data['is_for_adult'] ?? false);

        $entityManager->persist($novel);
        $entityManager->flush();

        return $this->json(['message' => 'Livre ajouté avec succès !'], 201);
    }

    #[Route('/update/{id}', methods: ['PUT'])]
    public function update(Novel $novel, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true);

        $novel->setName($data['name'] ?? $novel->getName());
        $novel->setAuthor($data['author'] ?? $novel->getAuthor());
        $novel->setAbstract($data['abstract'] ?? $novel->getAbstract());
        $novel->setIsPublished($data['is_published'] ?? $novel->getIsPublished());
        $novel->setReleasedAt(new \DateTime($data['released_at'] ?? $novel->getReleasedAt()->format('Y-m-d')));
        $novel->setSlug($data['slug'] ?? $novel->getSlug());
        $novel->setIsForAdult($data['is_for_adult'] ?? $novel->getIsForAdult());

        $entityManager->flush();

        return $this->json(['message' => 'Livre mis à jour avec succès !']);
    }

    #[Route('/delete/{id}', methods: ['DELETE'])]
    public function delete(Novel $novel, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager->remove($novel);
        $entityManager->flush();

        return $this->json(['message' => 'Livre supprimé avec succès !']);
    }

    #[Route('/check-availability/{id}', methods: ['GET'])]
    public function checkAvailability(Novel $novel, EntityManagerInterface $entityManager): JsonResponse
    {
        $isAvailable = !$entityManager->getRepository(RentingHistory::class)
                                      ->findOneBy(['novel' => $novel, 'end' => null]);

        return $this->json(['available' => $isAvailable]);
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

        // Créer l'emprunt
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
}
