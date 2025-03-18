<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookSearchType;
use App\Entity\RentingHistory;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\RentingHistoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/livres', name: 'app_book_')]
class BookController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private BookRepository $br, private RentingHistoryRepository $rhr) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $user = $this->getUser();

        if ($user && in_array('ROLE_ADULT', $user->getRoles())) {
            $books = $this->br->findAll() ?? [];;
        } else {
            $books = $this->br->findBy(["is_for_adult" => false]) ?? [];;
        }

        $form = $this->createForm(BookSearchType::class, null, [
            'user' => $user ?: null
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();

            // Récupérer les tags sélectionnés et les tags à exclure
            $tags = $criteria['tags'] ?? [];  // Ce sont des objets Tag ou IDs (selon le formulaire)
            $excludeTags = $criteria['excludeTags'] ?? [];  // Ce sont des IDs de tags à exclure
            // dd($criteria['excludeTags']);

            // Pour être sûr que excludeTags est bien un tableau d'IDs
            if (!empty($excludeTags) && is_string($excludeTags)) {
                $excludeTags = explode(',', $excludeTags);  // Si les tags sont sous forme de chaîne séparée par des virgules
            }

            $title = $criteria['title'] instanceof Book ? $criteria['title']->getTitle() : null;
            $author = $criteria['author'] instanceof Book ? $criteria['author']->getAuthor() : null;

            // Préparer les critères pour la recherche
            $criteria['tags'] = $tags;
            $criteria['excludeTags'] = $excludeTags;
            $criteria['title'] = $title;
            $criteria['author'] = $author;
            // dd($criteria);

            $books = $this->br->searchBooks($criteria) ?? [];
            // dd($books);

            if (!$user || !$user->isAdult()) {
                $books = array_filter($books, function ($book) {
                    return !$book->isForAdult();
                });
            }
        }

        return $this->render('book/index.html.twig', [
            'books' => $books,
            'form' => $form,
        ]);
    }

    #[Route('/{ref}', name: 'show', methods: ['GET'])]
    public function show(string $ref): Response
    {
        $book = $this->br->findOneBy(['ref' => $ref]);

        if (!$book) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('app_book_index');
        }

        $user = $this->getUser();
        if ($user) {
            if (!in_array('ROLE_ADULT', $user->getRoles())) {
                if ($book->isForAdult()) {
                    $this->addFlash('warning', 'Vous ne pouvez pas voir les détails de ce livre !');
                    return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
                }
            }

            $isLiked = $user ? $book->getLikes()->contains($user) : false;

            $existingRental = $this->rhr->createQueryBuilder('r')
                ->where('r.book = :book')
                ->andWhere('r.user = :user')
                ->andWhere('r.end >= :now')
                ->setParameter('book', $book)
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
            // TODO pb : faut un rechargement pour que le status soit mis à jour
            // TODO template à faire
            return $this->render('book/show.html.twig', [
                'book' => $book,
                'isLiked' => $isLiked,
                'isRented' => $isRented
            ]);
        } else {
            if ($book->isForAdult()) {
                $this->addFlash('warning', 'Vous ne pouvez pas voir les détails de ce livre !');
                return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('book/show.html.twig', [
                'book' => $book,
            ]);
        }
    }

    #[IsGranted('ROLE_VERIFIED')]
    #[Route('/{ref}/borrow', 'borrow', methods: ['POST'])]
    public function borrow(string $ref, Request $request): Response
    {
        $book = $this->br->findOneBy(['ref' => $ref]);

        if (!$book) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('app_book_index');
        }

        $user = $this->getUser();

        //inutile car contrainte sur la route
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour emprunter un livre.');
            return $this->redirectToRoute('app_login');
        }

        if (!in_array('ROLE_ADULT', $user->getRoles())) {
            if ($book->isForAdult()) {
                $this->addFlash('warning', 'Vous ne pouvez pas emprunter ce livre !');
                return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        if ($user->getRentedBooksCount() < 5) {
            $rental = new RentingHistory();
            $rental->setUser($user);
            $rental->setBook($book);
            $this->em->persist($rental);
            $this->em->flush();

            $this->addFlash('success', 'Livre emprunté avec succès !');

            // Récupérer l'URL précédente pour rediriger correctement
            $referer = $request->headers->get('referer');
            // Si l'URL du référent est disponible, rediriger l'utilisateur vers cette page
            if ($referer) {
                return $this->redirect($referer);
            } else {
                return $this->redirectToRoute('app_book_show', ['ref' => $book->getRef()], Response::HTTP_SEE_OTHER);
            }
        } else {
            $this->addFlash('danger', "Vous avez déjà 5 livres d'empruntés");
            $referer = $request->headers->get('referer');
            // Si l'URL du référent est disponible, rediriger l'utilisateur vers cette page
            if ($referer) {
                return $this->redirect($referer);
            } else {
                return $this->redirectToRoute('app_book_show', ['ref' => $book->getRef()], Response::HTTP_SEE_OTHER);
            }
        }
    }

    #[IsGranted('ROLE_VERIFIED')]
    #[Route('/{ref}/return', 'return', methods: ['POST'])]
    public function return(string $ref, Request $request): Response
    {
        $book = $this->br->findOneBy(['ref' => $ref]);

        if (!$book) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('app_book_index');
        }

        $user = $this->getUser();

        if (!$book) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('app_book_index');
        }

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté.');
            return $this->redirectToRoute('app_book_show', ['ref' => $book->getRef()], Response::HTTP_SEE_OTHER);
        }

        $rental = $this->rhr->findOneBy([
            'book' => $book,
            'user' => $user,
        ]);

        if (!$rental) {
            $this->addFlash('danger', 'Emprunt non trouvé.');
            return $this->redirectToRoute('app_book_show', ['ref' => $book->getRef()], Response::HTTP_SEE_OTHER);
        }

        $rental->setEnd(new \DateTimeImmutable());
        $rental->setUpdatedAt(new \DateTimeImmutable());
        // $rentedBookCount = $user->getRentedBooksCount();
        // $newRentedBookCount = $rentedBookCount - 1;
        // $user->setRentedBooksCount($newRentedBookCount);
        $user->setRentedBooksCount($user->getRentedBooksCount() - 1);

        // TODO : Rajouter un form pour que l'utilisateur ajoute à quelle page il s'est arrête quand il retourne le livre
        $this->em->flush();

        $this->addFlash('success', 'Livre retourné avec succès !');

        $referer = $request->headers->get('referer');
        // Si l'URL du référent est disponible, rediriger l'utilisateur vers cette page
        if ($referer) {
            return $this->redirect($referer);
        } else {
            return $this->redirectToRoute('app_book_show', ['ref' => $book->getRef()], Response::HTTP_SEE_OTHER);
        }
    }

    #[IsGranted('ROLE_VERIFIED')]
    #[Route('/{ref}/like', 'like', methods: ['POST'])]
    public function like(string $ref, Request $request): Response
    {
        $book = $this->br->findOneBy(['ref' => $ref]);

        if (!$book) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('app_book_index');
        }

        $user = $this->getUser();

        // Inutile car contrainte
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour mettre en favoris un livre.');
            return $this->redirectToRoute('app_book_show', ['ref' => $book->getRef()], Response::HTTP_SEE_OTHER);
        }

        if (!in_array('ROLE_ADULT', $user->getRoles())) {
            if ($book->isForAdult()) {
                $this->addFlash('warning', 'Vous ne pouvez pas mettre en favoris ce livre !');
                return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $book->addLike($user);
        // $this->em->persist($book);
        $this->em->flush();

        $this->addFlash('success', 'Ce livre a bien été rajouté à votre liste de favoris');

        $referer = $request->headers->get('referer');
        // Si l'URL du référent est disponible, rediriger l'utilisateur vers cette page
        if ($referer) {
            return $this->redirect($referer);
        } else {
            return $this->redirectToRoute('app_book_show', ['ref' => $book->getRef()], Response::HTTP_SEE_OTHER);
        }
    }

    #[IsGranted('ROLE_VERIFIED')]
    #[Route('/{ref}/unlike', 'unlike', methods: ['POST'])]
    public function unlike(string $ref, Request $request): Response
    {
        $book = $this->br->findOneBy(['ref' => $ref]);

        if (!$book) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('app_book_index');
        }

        $user = $this->getUser();

        //Normalement pas besoin car si pas d'user, pas de liste de fav
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour retirer un livre de la liste des favoris');
            return $this->redirectToRoute('app_book_index');
        }

        $book->removeLike($user);
        $this->em->flush();

        $this->addFlash('success', 'Ce livre a bien été retiré de la liste des favoris');

        $referer = $request->headers->get('referer');
        // Si l'URL du référent est disponible, rediriger l'utilisateur vers cette page
        if ($referer) {
            return $this->redirect($referer);
        } else {
            return $this->redirectToRoute('app_book_show', ['ref' => $book->getRef()], Response::HTTP_SEE_OTHER);
        }
    }

    #[IsGranted('ROLE_VERIFIED')]
    #[Route('/{ref}/pdf', 'pdf', methods: ['GET'])]
    public function viewPdf(string $ref): Response
    {
        //TODO faire template pour vérification
        $book = $this->br->findOneBy(['ref' => $ref]);

        if (!$book) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('app_book_index');
        }

        if (!$book) {
            $this->addFlash('danger', 'Roman non trouvé.');
            return $this->redirectToRoute('app_book_index');
        }

        $user = $this->getUser();

        if (!in_array('ROLE_ADULT', $user->getRoles())) {
            if ($book->isForAdult()) {
                $this->addFlash('warning', 'Vous ne pouvez pas lire ce livre !');
                return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        if (!$book->getFile() || !$book->isPublished()) {
            $this->addFlash('danger', 'PDF non disponible pour ce roman.');
            return $this->redirectToRoute('app_book_show', ['ref' => $book->getRef()], Response::HTTP_SEE_OTHER);
        }

        $existingRental = $this->rhr->createQueryBuilder('r')
            ->where('r.book = :book')
            ->andWhere('r.user = :user')
            ->andWhere('r.end >= :now')
            ->setParameter('book', $book)
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTimeImmutable())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($existingRental) {
            // TODO : A tester
            $pdfPath = $this->getParameter('kernel.project_dir') . '/public/' . $book->getFile();
            return new BinaryFileResponse($pdfPath);
        } else {
            $this->addFlash('danger', "Vous n'avez pas emprunter ce livre !");
            return $this->redirectToRoute('app_book_show', ['ref' => $book->getRef()], Response::HTTP_SEE_OTHER);
        }
    }
}
