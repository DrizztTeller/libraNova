<?php

namespace App\Controller;

use App\Entity\Novel;
use App\Entity\RentingHistory;
use App\Repository\NovelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/romans', name: 'novel_')]
class NovelController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager, NovelRepository $nr)
    {
        $this->em = $entityManager;
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $novels = $this->nr->findAll();

        return $this->render('novel/index.html.twig', [
            'novels' => $novels,
        ]);
    }

    #[Route('/{ref}', name: 'show', methods: ['GET'])]
    public function getNovel(string $ref): Response
    {
        $novels = $this->nr->findAll();

        if (!$novel) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('novel_index');
        }

        return $this->render('novel/show.html.twig', [
            'novel' => $novel,
        ]);
    }

    #[Route('/borrow/{ref}', name: 'borrow', methods: ['POST'])]
    public function borrow(string $ref): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour emprunter un livre.');
            return $this->redirectToRoute('novel_index');
        }

        $novel = $this->em->getRepository(Novel::class)->findOneBy(['ref' => $ref]);
        if (!$novel) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('novel_index');
        }

        $existingRental = $this->em->getRepository(RentingHistory::class)->findOneBy([
            'novel' => $novel,
            'end' => null
        ]);

        if ($existingRental) {
            $this->addFlash('danger', 'Ce livre est déjà emprunté.');
            return $this->redirectToRoute('novel_index');
        }

        $rental = new RentingHistory();
        $rental->setUser($user);
        $rental->setNovel($novel);
        $rental->setStart(new \DateTimeImmutable());

        $this->em->persist($rental);
        $this->em->flush();

        $this->addFlash('success', 'Livre emprunté avec succès !');
        return $this->redirectToRoute('novel_index');
    }

    #[Route('/return/{ref}', name: 'return', methods: ['POST'])]
    public function returnBook(string $ref): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté.');
            return $this->redirectToRoute('novel_index');
        }

        $novel = $this->em->getRepository(Novel::class)->findOneBy(['ref' => $ref]);
        if (!$novel) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('novel_index');
        }

        $rental = $this->em->getRepository(RentingHistory::class)->findOneBy([
            'novel' => $novel,
            'user' => $user,
            'end' => null
        ]);

        if (!$rental) {
            $this->addFlash('danger', 'Ce livre n’est pas en votre possession.');
            return $this->redirectToRoute('novel_index');
        }

        $rental->setEnd(new \DateTimeImmutable());
        $this->em->flush();

        $this->addFlash('success', 'Livre retourné avec succès !');
        return $this->redirectToRoute('novel_index');
    }

    #[Route('/like/{ref}', name: 'like', methods: ['POST'])]
    public function like(string $ref): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour liker un livre.');
            return $this->redirectToRoute('novel_index');
        }

        $novel = $this->em->getRepository(Novel::class)->findOneBy(['ref' => $ref]);
        if (!$novel) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('novel_index');
        }

        if ($novel->getUsersLiked()->contains($user)) {
            $this->addFlash('danger', 'Vous avez déjà liké ce livre.');
            return $this->redirectToRoute('novel_index');
        }

        $novel->addLike($user);
        $this->em->flush();

        $this->addFlash('success', 'Livre liké avec succès !');
        return $this->redirectToRoute('novel_index');
    }

    #[Route('/unlike/{ref}', name: 'unlike', methods: ['POST'])]
    public function unlike(string $ref): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour annuler un like.');
            return $this->redirectToRoute('novel_index');
        }

        $novel = $this->em->getRepository(Novel::class)->findOneBy(['ref' => $ref]);
        if (!$novel) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('novel_index');
        }

        if (!$novel->getUsersLiked()->contains($user)) {
            $this->addFlash('danger', 'Vous n’avez pas liké ce livre.');
            return $this->redirectToRoute('novel_index');
        }

        $novel->removeLike($user);
        $this->em->flush();

        $this->addFlash('success', 'Like annulé avec succès !');
        return $this->redirectToRoute('novel_index');
    }

    #[Route('/pdf/{ref}', name: 'pdf', methods: ['GET'])]
    public function viewPdf(string $ref): Response
    {
        $novel = $this->em->getRepository(Novel::class)->findOneBy(['ref' => $ref]);
        if (!$novel || !$novel->getPdfPath()) {
            $this->addFlash('danger', 'PDF non disponible pour ce roman.');
            return $this->redirectToRoute('novel_index');
        }

        $pdfPath = $this->getParameter('kernel.project_dir') . '/public/uploads/pdf/' . $novel->getPdfPath();
        return new BinaryFileResponse($pdfPath);
    }
}
