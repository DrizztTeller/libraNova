<?php

namespace App\Controller;

use App\Entity\Novel;
use App\Entity\RentingHistory;
use App\Repository\NovelRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Repository\RentingHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/romans', name: 'app_novel_')]
class NovelController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private NovelRepository $nr, private RentingHistoryRepository $rhr) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();

        if ($user && in_array('ROLE_ADULT', $user->getRoles())) {
            $novels = $this->nr->findAll();
        } else {
            $novels = $this->nr->findBy(["is_for_adult" => false]);
        }

        return $this->render('novel/index.html.twig', [
            'novels' => $novels,
        ]);
    }

    #[Route('/{ref}', name: 'show', methods: ['GET'])]
    public function getNovel(string $ref): Response
    {
        $novel = $this->nr->findOneBy(['ref' => $ref]);

        if (!$novel) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('app_novel_index');
        }


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
            'isRented' => $isRented
        ]);
    }

    #[IsGranted('ROLE_VERIFIED')]
    #[Route('/{ref}/borrow', 'borrow', methods: ['POST'])]
    public function borrow(string $ref): Response
    {
        $novel = $this->nr->findOneBy(['ref' => $ref]);

        if (!$novel) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('app_novel_index');
        }

        $user = $this->getUser();

        if (!$novel) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('app_novel_index');
        }

        //inutile car contrainte sur la route
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
        $rental->setEnd(new \DateTimeImmutable("+5 days"));
        $this->em->persist($rental);
        $this->em->flush();

        $this->addFlash('success', 'Livre emprunté avec succès !');

        return $this->redirectToRoute('app_novel_show', ['ref' => $novel->getRef()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{ref}/return', 'return', methods: ['POST'])]
    public function return(string $ref): Response
    {
        $novel = $this->nr->findOneBy(['ref' => $ref]);

        if (!$novel) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('app_novel_index');
        }

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

        if (!$rental) {
            $this->addFlash('danger', 'Emprunt non trouvé.');
            return $this->redirectToRoute('app_novel_show', ['ref' => $novel->getRef()], Response::HTTP_SEE_OTHER);
        }

        $rental->setEnd(new \DateTimeImmutable());
        $rental->setUpdatedAt(new \DateTimeImmutable());
        // TODO : Rajouter un form pour que l'utilisateur ajoute à quelle page il s'est arrête quand il retourne le livre
        $this->em->flush();

        $this->addFlash('success', 'Livre retourné avec succès !');

        // TODO : verif l'origine si sur page emprunts retour sur cette page et non sur celle de show
        return $this->redirectToRoute('app_novel_show', ['ref' => $novel->getRef()], Response::HTTP_SEE_OTHER);
    }

    #[IsGranted('ROLE_VERIFIED')]
    #[Route('/{ref}/like', 'like', methods: ['POST'])]
    public function like(string $ref): Response
    {
        $novel = $this->nr->findOneBy(['ref' => $ref]);

        if (!$novel) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('app_novel_index');
        }

        $user = $this->getUser();

        // Inutile car contrainte
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
        // $this->em->persist($novel);
        $this->em->flush();

        $this->addFlash('success', 'Ce livre a bien été rajouté à votre liste de favoris');

        // TODO : verif l'origine si sur page emprunts retour sur cette page et non sur celle de show

        return $this->redirectToRoute('app_novel_show', ['ref' => $novel->getRef()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{ref}/unlike', 'unlike', methods: ['POST'])]
    public function unlike(string $ref): Response
    {
        $novel = $this->nr->findOneBy(['ref' => $ref]);

        if (!$novel) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('app_novel_index');
        }

        $user = $this->getUser();

        //Normalement pas besoin car si pas d'user, pas de liste de fav
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour retirer un livre de la liste des favoris');
            return $this->redirectToRoute('app_novel_index');
        }

        $novel->removeLike($user);
        $this->em->flush();

        $this->addFlash('success', 'Ce livre a bien été retiré de la liste des favoris');

        // TODO : verif l'origine si sur page emprunts retour sur cette page et non sur celle de show

        return $this->redirectToRoute('app_novel_show', ['ref' => $novel->getRef()], Response::HTTP_SEE_OTHER);
    }

    #[IsGranted('ROLE_VERIFIED')]
    #[Route('/{ref}/pdf', 'pdf', methods: ['GET'])]
    public function viewPdf(string $ref): Response
    {
        $novel = $this->nr->findOneBy(['ref' => $ref]);

        if (!$novel) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('app_novel_index');
        }

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

        if (!$novel->getFile() || !$novel->isPublished()) {
            $this->addFlash('danger', 'PDF non disponible pour ce roman.');
            return $this->redirectToRoute('app_novel_show', ['ref' => $novel->getRef()], Response::HTTP_SEE_OTHER);
        }

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
            // TODO : A tester
            $pdfPath = $this->getParameter('kernel.project_dir') . '/public/uploads/pdf/' . $novel->getFile();
            return new BinaryFileResponse($pdfPath);
        } else {
            $this->addFlash('danger', "Vous n'avez pas emprunter ce livre !");
            return $this->redirectToRoute('app_novel_show', ['ref' => $novel->getRef()], Response::HTTP_SEE_OTHER);
        }
    }
}
