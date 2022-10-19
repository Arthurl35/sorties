<?php

namespace App\Controller;

use App\Entity\Site;

use App\Form\ProfilType;
use App\Repository\ParticipantRepository;
use App\Entity\Participant;
use App\Utils\Upload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profil', name: 'profil_')]
class ProfilController extends AbstractController
{
    #[Route('/detail/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(ParticipantRepository $participantRepository, $id = null): Response
    {
        $particpant = $participantRepository->find($id);
        return $this->render('profil/show.html.twig', [
            'participant' => $particpant
        ]);
    }

    #[Route('/modifier/{id}', name: 'edit', requirements: ['id' => '\d+'])]
    public function ModifierParticipant(Request $request,
                                        ParticipantRepository $participantRepository,
                                        Upload $upload,
                                        $id = null): Response
    {
        $participant = $participantRepository->find($id);

        $participantForm = $this->createForm(SortieType::class, $participant, ['data' => $participant]);

        $participantForm->handleRequest($request);

        if($participantForm->isSubmitted() && $participantForm->isValid()){

                //gestion de l'upload de l'image
                $backdrop = $participantForm->get('backdrop')->getData();
                $participant->setBackdrop($upload->saveFile($backdrop, $participant->getNom(), $this->getParameter('sorties_backdrop_dir')));


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
