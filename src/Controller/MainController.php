<?php

namespace App\Controller;

use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
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
    public function home(SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find(28);
        //foreach ($user->getSortiesInscrits() as $sortie){
        //    echo $sortie;
        //}
        //return $this->render('main/home.html.twig');

        $dureeSeconde = 100*60;

        $dateDebut = $sortie->getDateHeureDebut()->format('d-m-Y h:m:s');
        $timeStampDebut = strtotime($dateDebut);
        $timeStampFin = $timeStampDebut+$dureeSeconde;

        $dateNow = new \DateTime();

        $timeStampNow = (strtotime($dateNow->format('d-m-Y h:m:s')));

        echo "Debut";
        echo $timeStampDebut;
        echo "<br>";
        echo "Fin";
        echo $timeStampFin;
        echo "<br>";
        echo "NOW";
        echo $timeStampNow;
        echo "<br>";





        return $this->render('main/home.html.twig', ['sortie' => $sortie]);



    }


}
