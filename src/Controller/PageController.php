<?php

namespace App\Controller;

use App\Service\SearchService;
use App\Repository\BookRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

final class PageController extends AbstractController
{
    private $searchService;


    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(BookRepository $br, Request $request): Response
    {
        $user = $this->getUser();

        if ($user && $user->isAdult() === true) {
            $booksNewest = $br->searchBooks([
                'published_within_week' => true,
                'is_published' => true,
                'orderBy' => 'released_at',
                'orderDirection' => 'DESC',
            ]);

            $booksLatest = $br->searchBooks([
                'created_within_week' => true,
                'orderBy' => 'created_at',
                'orderDirection' => 'DESC',
            ]);

            $booksTop = $br->searchBooks([
                'likes' => true,
                'orderBy' => 'likes',
                'orderDirection' => 'DESC',
                'limit' => 10
            ]);
        } else {
            $booksNewest = $br->searchBooks([
                'published_within_week' => true,
                'is_published' => true,
                'is_for_adult' => false,
                'orderBy' => 'released_at',
                'orderDirection' => 'DESC',
            ]);

            $booksLatest = $br->searchBooks([
                'created_within_week' => true,
                'is_for_adult' => false,
                'orderBy' => 'created_at',
                'orderDirection' => 'DESC',
            ]);

            $booksTop = $br->searchBooks([
                'is_for_adult' => false,
                'likes' => true,
                'orderBy' => 'likes',
                'orderDirection' => 'DESC',
                'limit' => 10
            ]);
        }

        $referer = $request->headers->get('referer');
        // Si l'URL du référent est disponible, rediriger l'utilisateur vers cette page
        if (strpos($referer, '/login') !== false) {
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                return $this->redirect('/admin');
            } else {
                return $this->redirectToRoute('app_user_profile', [], Response::HTTP_SEE_OTHER);
            }
        } else {
            return $this->render('page/index.html.twig', [
                'booksNewest' => $booksNewest,
                'booksLatest' => $booksLatest,
                'booksTop' => $booksTop,
            ]);
        }
    }

    #[Route('/contact', name: 'contact', methods: ['GET'])]
    public function contact(): Response
    {
        return $this->render('page/contact.html.twig', []);
    }

    #[Route('/cgu', name: 'cgu', methods: ['GET'])]
    public function cgu(): Response
    {
        return $this->render('page/cgu.html.twig', []);
    }

    #[Route('/rgpd', name: 'rgpd', methods: ['GET'])]
    public function rgpd(): Response
    {
        return $this->render('page/rgpd.html.twig', []);
    }

    #[Route('/mentions-legales', name: 'm_l', methods: ['GET'])]
    public function m_l(): Response
    {
        return $this->render('page/m_l.html.twig', []);
    }
}
