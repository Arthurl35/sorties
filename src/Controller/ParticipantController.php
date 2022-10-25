<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use App\Utils\Upload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class ParticipantController extends AbstractController
{
    #[Route('/', name: 'app_participant_index', methods: ['GET'])]
    public function index(ParticipantRepository $participantRepository): Response
    {
        return $this->render('participant/index.html.twig', [
            'participants' => $participantRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_participant_new', methods: ['GET', 'POST'])]
    public function new(Request $request,
                        ParticipantRepository $participantRepository,
                        Upload $upload,
                        UserPasswordHasherInterface $userPasswordHasher,

                        $id = null): Response
    {
        $participant = new Participant();
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

                //gestion du mot de passe
                $participant->setPassword($userPasswordHasher->hashPassword($participant, $form->get('password')->getData()));

                //gestion du status administrateur du participant
                $isAdmin = $form->get('administrateur')->getData();
                if($isAdmin){
                    $participant->setRoles(['ROLE_ADMIN']);
                }else{
                    $participant->setRoles(['ROLE_USER']);
                }


                //gestion de l'upload de l'image
                $backdrop = $form->get('backdrop')->getData();
                $participant->setBackdrop($upload->saveFile($backdrop, $participant->getNom(), $this->getParameter('sorties_backdrop_dir')));


                //enregistrement des données
                $participantRepository->add($participant, true);

                //feedback user
                $this->addFlash('success', 'Participant' . $id . ' a été ajouté !');

            $participantRepository->save($participant, true);

            return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('participant/new.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/new_csv', name: 'app_participant_new_csv', methods: ['GET', 'POST'])]
    public function new_csv(Request $request,
                        ParticipantRepository $participantRepository,
                        Upload $upload,
                        UserPasswordHasherInterface $userPasswordHasher,

        $id = null): Response
    {
        $participant = new Participant();
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {



            return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('participant/new.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participant_show', methods: ['GET'])]
    public function show(Participant $participant): Response
    {
        return $this->render('participant/show.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_participant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Participant $participant, ParticipantRepository $participantRepository, Upload $upload,): Response
    {
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //gestion de l'upload de l'image
            $backdrop = $form->get('backdrop')->getData();
            if($backdrop) {
                $participant->setBackdrop($upload->saveFile($backdrop, $participant->getNom(), $this->getParameter('sorties_backdrop_dir')));
            }

            //enregistrement des données
            $participantRepository->add($participant, true);


            $participantRepository->save($participant, true);



            return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('participant/edit.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participant_delete', methods: ['POST'])]
    public function delete(Request $request, Participant $participant, ParticipantRepository $participantRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$participant->getId(), $request->request->get('_token'))) {
            $participantRepository->remove($participant, true);
        }

        return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
    }
}
