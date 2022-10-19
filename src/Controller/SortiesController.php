<?php

namespace App\Controller;

    use App\Entity\Etat;
    use App\Entity\Lieu;
    use App\Entity\Participant;
    use App\Entity\Sortie;
    use App\Form\LieuType;
    use App\Form\SortieType;
    use App\Repository\EtatRepository;
    use App\Repository\LieuRepository;
    use App\Repository\ParticipantRepository;
    use App\Repository\SortieRepository;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;

//préfixe de toutes mes routes du controller
#[Route('/sorties', name: 'sortie_')]
class SortiesController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->findAll();

        return $this->render('sorties/list.html.twig', [
            'sorties' => $sorties
        ]);
    }

    #[Route('/add', name: 'add')]
    #[Route('/edit/{id}', name: 'edit', requirements: ['id' => '\d+'])]
    public function add(Request $request, SortieRepository $sortieRepository, EtatRepository $etatRepository, ParticipantRepository $participantRepository, LieuRepository $lieuRepository,int $id = null): Response
    {
        //simule un user co
        $user = $participantRepository->find(11);

        //récupère les états existants
        $etatEnregistrer = $etatRepository->find(1);

        if ($id) {
            $sortie = $sortieRepository->find($id);
            $lieu = $sortie->getLieu();
        } else {
            $sortie = new Sortie();
            $lieu = new Lieu();
        }

        $sortieForm = $this->createForm(SortieType::class, $sortie, ['data' => $sortie]);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            //traitement des données

            //mise à jour de l'entité
            $sortie->setNom($sortieForm->get('nom')->getData());
            $sortie->setDateHeureDebut($sortieForm->get('dateHeureDebut')->getData());
            $sortie->setDuree($sortieForm->get('duree')->getData());
            $sortie->setDateLimiteInscription($sortieForm->get('dateLimiteInscription')->getData());
            $sortie->setNbInscriptionMax($sortieForm->get('nbInscriptionMax')->getData());
            $sortie->setInfosSortie($sortieForm->get('infosSortie')->getData());
            $sortie->setLieu($sortieForm->get('lieu')->getData());

            $sortie->setSite($user->getSite());
            $sortie->setOrganisateur($user);


            //1 Créée
            //2 Ouverte
            //3 Clôturée
            //4 Activité en cours
            //5 Passé
            //6 Annulée

            if ($sortieForm->get('enregistrer') != null) {

                if ($sortie->getEtat() != null && $sortie->getEtat()->getId() != 1) {
                    $this->addFlash('error', 'action impossible sur une sortie ' . $sortie->getEtat()->getLibelle() . ' !');

                    return $this->render('sorties/edit.html.twig', [
                        'sortieForm' => $sortieForm->createView()
                    ]);
                } else {
                    $sortie->setEtat($etatEnregistrer);

                    //enregistrement des données
                    if($sortieRepository->findBy(['nom' => $sortie->getNom()])) {
                        $this->addFlash('error', 'une sortie existe déjà sous ce nom !');

                        return $this->render('sorties/add.html.twig', [
                            'sortieForm' => $sortieForm->createView()
                        ]);
                    }
                    else if($sortie->getDateHeureDebut() < new \DateTime() && $sortie->getDateLimiteInscription() < new \DateTime()){
                        $this->addFlash('error', 'La date renseigné correspond au passé !');

                        return $this->render('sorties/add.html.twig', [
                            'sortieForm' => $sortieForm->createView()
                        ]);
                    }
                    else if($sortie->getDateHeureDebut() < $sortie->getDateLimiteInscription()){
                        $this->addFlash('error', 'La date limite d\'inscription ne peux pas être antérieur à la date de la sortie !');

                        return $this->render('sorties/add.html.twig', [
                            'sortieForm' => $sortieForm->createView()
                        ]);
                    }
                    else {
                        $sortieRepository->save($sortie, true);
                        $this->addFlash('success', 'sortie créée !');
                    }
                }
            }

            //redirection vers la page de détail
            //return $this->redirectToRoute('sortie_show', ['id' => $sortie->getId()]);
            return $this->redirectToRoute('sortie_index');

        }
        if ($id) {
            return $this->render('sorties/edit.html.twig', [
                'sortieForm' => $sortieForm->createView()
            ]);
        } else {
            return $this->render('sorties/add.html.twig', [
                'sortieForm' => $sortieForm->createView()
            ]);
        }
    }

}
