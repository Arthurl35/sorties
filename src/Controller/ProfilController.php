<?php

namespace App\Controller;

use App\Form\ProfilType;
use App\Repository\ParticipantRepository;
use App\Entity\Participant;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profil', name: 'profil_')]
class ProfilController extends AbstractController
{
    #[Route('/detail/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(ParticipantRepository $participantRepository, $id): Response
    {
        return $this->render('profil/home.html.twig', [
            '$participantAffichage' => '$participantRepository',
        ]);
    }

    #[Route('/modifier/{id}', name: 'edit', requirements: ['id' => '\d+'])]
    public function ModifierParticipant(Request $request,
                                        ParticipantRepository $participantRepository,
                                        $id = null): Response
    {
        $participant = $participantRepository->find($id);

        $participantForm = $this->createForm(ProfilType::class, $participant, ['data' => $participant]);

        $participantForm->handleRequest($request);

        if($participantForm->isSubmitted() && $participantForm->isValid()){

                //gestion de l'upload de l'image

                //enregistrement des données
                $participantRepository->add($participant, true);

                //feedback user
                $this->addFlash('success', 'Participant' . $id . 'a été modifié !');
            }



        return $this->render('profil/edit.html.twig', [
            'profilForm' => $participantForm->createView(),
        ]);
    }
}
