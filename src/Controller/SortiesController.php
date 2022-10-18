<?php

namespace App\Controller;

use App\Entity\Site;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sorties', name: 'sorties_')]
class SortiesController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function list(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->findAll();

        return $this->render('sorties/list.html.twig', [
            'sorties' => $sorties
        ]);
    }
}
