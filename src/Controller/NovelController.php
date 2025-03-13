<?php

namespace App\Controller;

use App\Entity\Novel;
use App\Entity\RentingHistory;
use App\Repository\NovelRepository;
use App\Repository\RentingHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/romans', name: 'app_novel_')]
class NovelController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private NovelRepository $nr, private RentingHistoryRepository $rhr) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();

        if (in_array('ROLE_ADULT', $user->getRoles())) {
            $novels = $this->nr->findAll();
        } else {
            $novels = $this->nr->findBy(["is_for_adult" => false]);
        }

        return $this->render('novel/index.html.twig', [
            'novels' => $novels,
        ]);
    }

    #[Route('/{ref}', name: 'show', methods: ['GET'])]
    public function getNovel(Novel $novel): Response
    {

        if (!$novel) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('app_novel_index');
        }

        $user = $this->getUser();

        if (!in_array('ROLE_ADULT', $user->getRoles())) {
            if ($novel->isForAdult()) {
                $this->addFlash('warning', 'Vous ne pouvez pas voir les détails de ce livre !');
                return $this->redirectToRoute('app_novel_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $isLiked = $user ? $novel->getLikes()->contains($user) : false;

        $existingRental = $this->rhr->createQueryBuilder('r')
            ->where('r.novel = :novel')
            ->andWhere('r.user = :user')
            ->andWhere('r.end >= :now')
            ->setParameter('novel', $novel)
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTimeImmutable())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($existingRental) {
            $isRented = true;
        } else {
            $isRented = false;
        }

        return $this->render('novel/show.html.twig', [
            'novel' => $novel,
            'isLiked' => $isLiked,
            'isRented'=> $isRented
        ]);
    }

    #[Route('/{ref}', name: 'borrow', methods: ['POST'])]
    public function borrow(Novel $novel): Response
    {
        $user = $this->getUser();

        if (!$novel) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('app_novel_index');
        }

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour emprunter un livre.');
            return $this->redirectToRoute('app_novel_show', ['ref' => $novel->getRef()], Response::HTTP_SEE_OTHER);
        }

        if (!in_array('ROLE_ADULT', $user->getRoles())) {
            if ($novel->isForAdult()) {
                $this->addFlash('warning', 'Vous ne pouvez pas emprunter ce livre !');
                return $this->redirectToRoute('app_novel_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $rental = new RentingHistory();
        $rental->setUser($user);
        $rental->setNovel($novel);
        $rental->setStart(new \DateTimeImmutable());

        $this->em->persist($rental);
        $this->em->flush();

        $this->addFlash('success', 'Livre emprunté avec succès !');

        return $this->redirectToRoute('app_novel_show', ['ref' => $novel->getRef()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{ref}', name: 'return', methods: ['POST'])]
    public function returnBook(Novel $novel): Response
    {
        $user = $this->getUser();

        if (!$novel) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('app_novel_index');
        }

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté.');
            return $this->redirectToRoute('app_novel_show', ['ref' => $novel->getRef()], Response::HTTP_SEE_OTHER);
        }

        if (!in_array('ROLE_ADULT', $user->getRoles())) {
            if ($novel->isForAdult()) {
                $this->addFlash('warning', 'Vous ne pouvez pas emprunter ce livre !');
                return $this->redirectToRoute('app_novel_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $rental = $this->rhr->findOneBy([
            'novel' => $novel,
            'user' => $user,
        ]);

        $rental->setEnd(new \DateTimeImmutable());
        $rental->setUpdatedAt(new \DateTimeImmutable());
        // TODO : Rajouter un form pour que l'utilisateur ajoute à quelle page il s'est arrête quand il retourne le livre
        $this->em->flush();

        $this->addFlash('success', 'Livre retourné avec succès !');
        return $this->redirectToRoute('app_novel_show', ['ref' => $novel->getRef()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{ref}', name: 'like', methods: ['POST'])]
    public function like(Novel $novel): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour mettre en favoris un livre.');
            return $this->redirectToRoute('app_novel_show', ['ref' => $novel->getRef()], Response::HTTP_SEE_OTHER);
        }

        if (!in_array('ROLE_ADULT', $user->getRoles())) {
            if ($novel->isForAdult()) {
                $this->addFlash('warning', 'Vous ne pouvez pas mettre en favoris ce livre !');
                return $this->redirectToRoute('app_novel_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $novel->addLike($user);
        $this->em->flush();

        $this->addFlash('success', 'Ce livre a bien été rajouté à votre liste de favoris');
        return $this->redirectToRoute('app_novel_show', ['ref' => $novel->getRef()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{ref}', name: 'unlike', methods: ['POST'])]
    public function unlike(Novel $novel): Response
    {
        $user = $this->getUser();

        //Normalement pas besoin car si pas d'user, pas de liste de fav
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour retirer un livre de la liste des favoris');
            return $this->redirectToRoute('app_novel_index');
        }

        $novel->removeLike($user);
        $this->em->flush();

        $this->addFlash('success', 'Ce livre a bien été retiré de la liste des favoris');
        return $this->redirectToRoute('app_novel_show', ['ref' => $novel->getRef()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{ref}/pdf', name: 'pdf', methods: ['GET'])]
    public function viewPdf(Novel $novel): Response
    {
        if (!$novel) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('app_novel_index');
        }

        $user = $this->getUser();

        if (!in_array('ROLE_ADULT', $user->getRoles())) {
            if ($novel->isForAdult()) {
                $this->addFlash('warning', 'Vous ne pouvez pas lire ce livre !');
                return $this->redirectToRoute('app_novel_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        if (!$novel->getFile()) {
            $this->addFlash('danger', 'PDF non disponible pour ce roman.');
            return $this->redirectToRoute('app_novel_show', ['ref' => $novel->getRef()], Response::HTTP_SEE_OTHER);
        }

        // TODO : A tester
        $pdfPath = $this->getParameter('kernel.project_dir') . '/public/uploads/pdf/' . $novel->getFile();
        return new BinaryFileResponse($pdfPath);
    }
}
