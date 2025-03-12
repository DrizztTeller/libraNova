<?php

namespace App\Controller;

use DateTime;
use App\Service\SearchService;
use App\Repository\NovelRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class PageController extends AbstractController
{
    private $searchService;


    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(NovelRepository $nr): Response
    {
        $user = $this->getUser();

        if ($user && $user->isAdult() === true) {
            $novelsNewest = $nr->searchNovels([
                'published_within_week' => true,
                'is_published' => true,
                'orderBy' => 'released_at',
                'orderDirection' => 'DESC',
            ]);

            $novelsLatest = $nr->searchNovels([
                'created_within_week' => true,
                'orderBy' => 'created_at',
                'orderDirection' => 'DESC',
            ]);

            $novelsTop = $nr->searchNovels([
                'orderBy' => 'likes',
                'orderDirection' => 'DESC',
                'limit' => 10
            ]);
        } else {
            $novelsNewest = $nr->searchNovels([
                'published_within_week' => true,
                'is_published' => true,
                'is_for_adult' => false,
                'orderBy' => 'released_at',
                'orderDirection' => 'DESC',
            ]);

            $novelsLatest = $nr->searchNovels([
                'created_within_week' => true,
                'is_for_adult' => false,
                'orderBy' => 'created_at',
                'orderDirection' => 'DESC',
            ]);

            $novelsTop = $nr->searchNovels([
                'is_for_adult' => false,
                'orderBy' => 'likes',
                'orderDirection' => 'DESC',
                'limit' => 10
            ]);
        }


        return $this->render('page/index.html.twig', [
            'novelsNewest' => $novelsNewest,
            'novelsLatest' => $novelsLatest,
            'novelsTop' => $novelsTop,
        ]);
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
