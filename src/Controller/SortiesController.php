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
            'sorties' => $sorties,
        ]);
    }

    #[Route('/add', name: 'add')]
    #[Route('/edit/{id}', name: 'edit', requirements: ['id' => '\d+'])]
    public function addOrEdit(Request $request, SortieRepository $sortieRepository, EtatRepository $etatRepository, ParticipantRepository $participantRepository, LieuRepository $lieuRepository, int $id = null): Response
    {
        //simule un user co
        $user = $participantRepository->find(11);

        //récupère les états existants
        $etatCree = $etatRepository->find(1);
        $etatOuvert = $etatRepository->find(2);
        $etatCloture = $etatRepository->find(3);
        $etatEnCours = $etatRepository->find(4);
        $etatPasse = $etatRepository->find(5);
        $etatAnnule = $etatRepository->find(6);

        $etatAutorise = [1];

        //libelle du btn submit à écouter
        $libelleSubmit = "enregistrer";

        if ($id) {
            $sortie = $sortieRepository->find($id);
            //si l'état est différent de 1
            if (!in_array($sortie->getEtat()->getId(), $etatAutorise)) return $this->redirectToRoute('sortie_index');
            $libelleSubmit = "modifier";
        } else {
            $sortie = new Sortie();
            $sortie->setEtat($etatCree);
        }

        $sortieForm = $this->createForm(SortieType::class, $sortie, ['data' => $sortie]);

        //selon création ou édition et l'état si édition on supprime des éléments du form
        if ($id) {
            $sortieForm->remove("enregistrer");
        } else {
            $sortieForm->remove("modifier");
            $sortieForm->remove("annuler");
            $sortieForm->remove("supprimer");
        }

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

            //Gestion Création Publication Modification
            if ($sortieForm->get($libelleSubmit)->isClicked()) {

                //si enregistrement set etat enregirée sinon set etat ouvert
                if ($libelleSubmit == "enregistrer") {
                    $messageValid = 'sortie Créée !';
                }
                if ($libelleSubmit == "modifier") {
                    $messageValid = 'sortie Modifiée !';
                }

                //traitement des données
                if ($sortieRepository->findBy(['nom' => $sortie->getNom()]) && $libelleSubmit != "modifier") {
                    $this->addFlash('error', 'une sortie existe déjà sous ce nom !');

                    return $this->render('sorties/add.html.twig', [
                        'sortieForm' => $sortieForm->createView()
                    ]);
                } else if ($sortie->getDateHeureDebut() < new \DateTime() && $sortie->getDateLimiteInscription() < new \DateTime()) {
                    $this->addFlash('error', 'La date renseigné correspond au passé !');

                    return $this->render('sorties/add.html.twig', [
                        'sortieForm' => $sortieForm->createView()
                    ]);
                } else if ($sortie->getDateHeureDebut() < $sortie->getDateLimiteInscription()) {
                    $this->addFlash('error', 'La date limite d\'inscription ne peux pas être antérieur à la date de la sortie !');

                    return $this->render('sorties/add.html.twig', [
                        'sortieForm' => $sortieForm->createView()
                    ]);
                } else {
                    //vérifie si nouveau lieu ou non
                    if ($sortie->getLieu()->getId() == 0) {

                        //vérifie toutes les infos sont renseignées
                        if (!empty($sortieForm->get('nom_lieu')) && !empty($sortieForm->get('rue_lieu')) && !empty($sortieForm->get('ville_lieu')) && !empty($sortieForm->get('cp_lieu')) && !empty($sortieForm->get('latitude_lieu')) && !empty($sortieForm->get('longitude_lieu'))) {
                            $lieu = new Lieu();
                            $lieu->setNom($sortieForm->get('nom_lieu')->getData());
                            $lieu->setRue($sortieForm->get('rue_lieu')->getData());
                            $lieu->setVille($sortieForm->get('ville_lieu')->getData());
                            $lieu->setCp($sortieForm->get('cp_lieu')->getData());
                            $lieu->setLatitude($sortieForm->get('latitude_lieu')->getData());
                            $lieu->setLongitude($sortieForm->get('longitude_lieu')->getData());

                            if ($lieuRepository->findBy(['nom' => $lieu->getNom()])) {
                                $this->addFlash('error', 'un lieu existe déjà sous ce nom, veuillez changer de nom ou le sélectionner directement dans la liste');

                                return $this->render('sorties/add.html.twig', [
                                    'sortieForm' => $sortieForm->createView()
                                ]);
                            } else {
                                $lieuRepository->save($lieu, true);
                                $sortie->setLieu($lieu);
                            }
                        } else {
                            $this->addFlash('error', 'vous devez renseigner toutes les infos du lieu');

                            return $this->render('sorties/add.html.twig', [
                                'sortieForm' => $sortieForm->createView()
                            ]);
                        }
                    }

                    //update des données
                    $sortieRepository->save($sortie, true);
                    $this->addFlash('success', $messageValid);

                    //redirection vers la page de détail
                    return $this->redirectToRoute('sortie_edit', ['id' => $sortie->getId()]);
                }
            }elseif ($sortieForm->get('annuler')->isClicked()){
                $sortie->setEtat($etatAnnule);
                //update des données
                $sortieRepository->save($sortie, true);
                $this->addFlash('success', 'sortie Annulée !');
            }
            elseif(($sortieForm->get('supprimer')->isClicked())){
                //delete des données
                $sortieRepository->remove($sortie, true);
                $this->addFlash('success', 'sortie Supprimer !');
            }
            //redirection vers la page de détail
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
