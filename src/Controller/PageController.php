<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class PageController extends AbstractController{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        return $this->render('page/index.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }

    #[Route('/contact', name: 'contact', methods: ['GET'])]
    public function contact(): Response
    {
        return $this->render('page/contact.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }

    #[Route('/cgu', name: 'cgu', methods: ['GET'])]
    public function cgu(): Response
    {
        return $this->render('page/cgu.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }

    #[Route('/rgpd', name: 'rgpd', methods: ['GET'])]
    public function rgpd(): Response
    {
        return $this->render('page/rgpd.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }

    #[Route('/mentions-legales', name: 'm_l', methods: ['GET'])]
    public function m_l(): Response
    {
        return $this->render('page/m_l.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }
}
