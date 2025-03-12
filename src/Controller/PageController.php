<?php

namespace App\Controller;

use App\Repository\NovelRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class PageController extends AbstractController{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(NovelRepository $nr): Response
    {
        $novelsNewest = $nr->searchNovels([
            'published_within_week' => true,
            'is_published' => true,
            'orderBy' => 'published_at',
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

        return $this->render('page/index.html.twig', [
            'novelsNewest' => $novelsNewest,
            'novelsLatest' => $novelsLatest,
            'novelsTop' => $novelsTop,
        ]);
    }

    #[Route('/contact', name: 'contact', methods: ['GET'])]
    public function contact(): Response
    {
        return $this->render('page/contact.html.twig', [
        ]);
    }

    #[Route('/cgu', name: 'cgu', methods: ['GET'])]
    public function cgu(): Response
    {
        return $this->render('page/cgu.html.twig', [
        ]);
    }

    #[Route('/rgpd', name: 'rgpd', methods: ['GET'])]
    public function rgpd(): Response
    {
        return $this->render('page/rgpd.html.twig', [
        ]);
    }

    #[Route('/mentions-legales', name: 'm_l', methods: ['GET'])]
    public function m_l(): Response
    {
        return $this->render('page/m_l.html.twig', [
        ]);
    }
}
