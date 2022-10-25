<?php

namespace App\Controller;

use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    //attributs de route pour php >= 8.0
    #[Route('/', name: 'main_home')]
    //exemple d'annotations de route pour php < 8.0
        /**
         * @Route("/main", name="main_home_2")
         */
    public function home(ParticipantRepository $participantRepository): Response
    {
        $user = $participantRepository->find(21);
        //foreach ($user->getSortiesInscrits() as $sortie){
        //    echo $sortie;
        //}
        //return $this->render('main/home.html.twig');
        return $this->redirectToRoute('sortie_index');
    }


}
