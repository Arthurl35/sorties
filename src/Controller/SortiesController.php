<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Filter;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\FilterType;
use App\Form\LieuType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use phpDocumentor\Reflection\Types\Collection;
use App\Utils\MajEtatSorties;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


//préfixe de toutes mes routes du controller
#[Route('/sorties', name: 'sortie_')]
class SortiesController extends AbstractController
{

    public function __construct()
    {

    }


    #[Route('', name: 'index')]
    public function index(SortieRepository $sortieRepository,
                          EtatRepository $etatRepository,
                          Request $request,
                          PaginatorInterface $paginator,
                            ParticipantRepository $participantRepository): Response {

        $this->majEtatSorties($sortieRepository, $etatRepository);
        $user = $this->getUserSession($request, $participantRepository);
        $sorties = $sortieRepository->findAll();

        $filter = new Filter();

        //Filtres
        $filterForm = $this->createForm(FilterType::class, $filter, ['data' => $filter]);
        $filterForm->handleRequest($request);

        //handle permet de savoir dans quel cas nous sommes
        if($filterForm->isSubmitted() && $filterForm->isValid()){

            $filter->setSite($filterForm->get('site')->getData());
            if($filter->getSite()->getId() == 0) $filter->setSite(null);

            $filter->setNom($filterForm->get('nom')->getData());
            $filter->setDateHeureDebut($filterForm->get('dateHeureDebut')->getData());
            $filter->setDateHeureFin($filterForm->get('dateHeureFin')->getData());
            $filter->setSortieOrganisateur($filterForm->get('sortieOrganisateur')->getData());
            $filter->setSortieInscrit($filterForm->get('sortieInscrit')->getData());
            $filter->setSortiePasInscrit($filterForm->get('sortiePasInscrit')->getData());
            $filter->setSortiePasse($filterForm->get('sortiePasse')->getData());

            $response = $sortieRepository->findByFilter($filter, $user->getId());


            unset($sorties);
            foreach ($response as $row){
                $sortie = new sortie();
                $sortie->setId($row['id']);
                $sortie->setNom($row['nom']);
                //$sortie1->setDateHeureDebut($row['date_heure_debut']);
                $sortie->setDateHeureDebut(new \DateTime());
                $sortie->setDuree($row['duree']);
                //$sortie1->setDateLimiteInscription($row['date_limite_inscription']);
                $sortie->setDateLimiteInscription(new \DateTime());

                $sortie->setNbInscriptionMax($row['nb_inscription_max']);
                $sortie->setInfosSortie($row['infos_sortie']);
                $sortie->setLieu($lieuRepository->find($row['lieu_id']));
                $sortie->setEtat($etatRepository->find($row['etat_id']));
                $sortie->setSite($siteRepository->find($row['site_id']));
                $sortie->setOrganisateur($participantRepository->find($row['organisateur_id']));
                $sorties[] = $sortie;
            }
        }

            $sorties = $paginator->paginate(
            $sorties,
            $request->query->getInt('page', 1),
            5);

        return $this->render('sorties/list.html.twig', [
            'filterForm' => $filterForm->createView(),
            'sorties' => $sorties,
        ]);
    }

    #[Route('/show/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(Request $request, SortieRepository $sortieRepository, ParticipantRepository $participantRepository, EtatRepository $etatRepository, $id): Response
    {
        $this->majEtatSorties($sortieRepository, $etatRepository);
        $user = $this->getUserSession($request, $participantRepository);
        $sortie = $sortieRepository->find($id);

        return $this->render('sorties/show.html.twig', [
            'sortie' => $sortie,
            'inscrit' => $this->isInscrit($user, $sortie)
        ]);
    }

    #[Route('/add', name: 'add')]
    #[Route('/edit/{id}', name: 'edit', requirements: ['id' => '\d+'])]
    public function addOrEdit(Request $request, SortieRepository $sortieRepository, EtatRepository  $etatRepository, ParticipantRepository $participantRepository, LieuRepository $lieuRepository, int $id = null): Response
    {
        //récupère le user
        $user = $this->getUserSession($request, $participantRepository);

        //var_dump($user);

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
            //test si le créateur est l'actuel demandeur de modification
            if(!$sortie->getOrganisateur()->getId() == $user->getId()) return $this->redirectToRoute('sortie_index');
            //si l'état est différent de 1
            if (!in_array($sortie->getEtat()->getId(), $etatAutorise)) return $this->redirectToRoute('sortie_index');
            $libelleSubmit = "modifier";
        } else {
            $sortie = new Sortie();
            $sortie->setEtat($etatCree);
            //on inscrit l'organisateur à la sortie
            $sortie->getParticipants()->add($user);
        }

        $sortieForm = $this->createForm(SortieType::class, $sortie, ['data' => $sortie]);

        //selon création ou édition et l'état si édition on supprime des éléments du form
        if ($id) {
            $sortieForm->remove("enregistrer");
        } else {
            $sortieForm->remove("modifier");
            $sortieForm->remove("publier");
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
            if($libelleSubmit == "modifier" and $sortieForm->get("publier")->isClicked()) $libelleSubmit = "publier";

            if ($sortieForm->get($libelleSubmit)->isClicked()) {

                //si enregistrement set etat enregirée sinon set etat ouvert
                if ($libelleSubmit == "enregistrer") {
                    $messageValid = 'sortie Créée !';
                }
                else if ($libelleSubmit == "modifier") {
                    $messageValid = 'sortie Modifiée !';
                }

                //traitement des données
                if ($sortieRepository->findBy(['nom' => $sortie->getNom()]) && $libelleSubmit != "modifier" && $libelleSubmit != "publier") {
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
                    if($libelleSubmit == "publier") return $this->redirectToRoute('sortie_publish', ['id' => $sortie->getId()]);
                    //update des données
                    $sortieRepository->save($sortie, true);
                    $this->addFlash('success', $messageValid);

                    //redirection vers la page de détail
                    return $this->redirectToRoute('sortie_edit', ['id' => $sortie->getId()]);
                }
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

    #[Route('/addRegister/{idUser}&{idSortie}', name: 'addRegister', requirements: ['idUser' => '\d+', 'idSortie' => '\d+'])]
    public function addRegister(Request $request, SortieRepository $sortieRepository, ParticipantRepository $participantRepository, EtatRepository $etatRepository, int $idUser, int $idSortie): Response
    {
        $this->majEtatSorties($sortieRepository, $etatRepository);

        //test si sortie existe et encort ouvert aux inscriptions
        $sortie = $sortieRepository->find($idSortie);
        if($sortie){
            $etat = $sortie->getEtat();
            if($etat->getId() != 1){
                if($etat->getId() != 2){
                    $this->addFlash('error', 'La sortie n\'accepte plus d\'inscription');
                    return $this->redirectToRoute('sortie_index');
                }
            }
            else {
                $this->addFlash('error', 'La sortie n\'est pas ouvert aux inscription');
                return $this->redirectToRoute('sortie_index');
            }
        }
        else $this->addFlash('error', 'la sortie n\'existe pas !');

        //test si utilisateur correspond et n'est pas déjà inscrit
        $user = $this->getUserSession($request, $participantRepository);
        if($user->getId() == $idUser){
            if(!$this->isInscrit($user, $sortie)){
                //on inscrit
                $sortie->getParticipants()->add($user);
                //update des données
                $sortieRepository->save($sortie, true);

                $this->addFlash('success', 'Inscription réussi !');
            }
            else $this->addFlash('error', 'vous êtes déjà inscrit !');
        }
        else $this->addFlash('error', 'tentative non autorisée !');

        return $this->redirectToRoute('sortie_index');
    }

    #[Route('/removeRegister/{idUser}&{idSortie}', name: 'removeRegister', requirements: ['idUser' => '\d+', 'idSortie' => '\d+'])]
    public function removeRegister(Request $request, SortieRepository $sortieRepository, ParticipantRepository $participantRepository, EtatRepository $etatRepository, int $idUser, int $idSortie): Response
    {
        $this->majEtatSorties($sortieRepository, $etatRepository);
        //test si sortie existe et encore ouvert aux inscriptions
        $sortie = $sortieRepository->find($idSortie);
        if($sortie){
            $etat = $sortie->getEtat();
            if($etat->getId() != 1){
                if($etat->getId() != 2 && $etat->getId() != 3){
                    $this->addFlash('error', 'Vous ne pouvez plus vous désinscrire !');
                    return $this->redirectToRoute('sortie_index');
                }
            }
            else {
                $this->addFlash('error', 'La sortie n\'est pas ouvert aux désinscription');
                return $this->redirectToRoute('sortie_index');
            }
        }
        else $this->addFlash('error', 'la sortie n\'existe pas !');

        //test si utilisateur correspond et n'est pas déjà inscrit
        $user = $this->getUserSession($request, $participantRepository);
        if($user->getId() == $idUser){
            if($this->isInscrit($user, $sortie)){
               if($sortie->getOrganisateur()->getId() == $user->getId()) $this->addFlash('error', 'vous ne pouvez vous désinscrire si vous êtes l\'organisateur');
               else{
                   //on désinscrit
                   $sortie->getParticipants()->remove($sortie->getParticipants()->indexOf($user));
                   //update des données
                   $sortieRepository->save($sortie, true);

                   $this->addFlash('success', 'Désinscription réussi !');
               }
            }
            else $this->addFlash('error', 'vous êtes n\'êtes pas inscrit !');
        }
        else $this->addFlash('error', 'tentative non autorisée !');

        return $this->redirectToRoute('sortie_index');
    }

    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '\d+'])]
    public function delete(Request $request, SortieRepository $sortieRepository, ParticipantRepository $participantRepository, EtatRepository $etatRepository, int $id): Response
    {
        $this->majEtatSorties($sortieRepository, $etatRepository);
        $etatAutorise = [1];

        $user = $this->getUserSession($request, $participantRepository);
        $sortie = $sortieRepository->find($id);

        if($sortie){
            if(!in_array($sortie->getEtat()->getId(), $etatAutorise)) {
                $this->addFlash('error', 'Action impossible sur une sortie '.$sortie->getEtat()->getLibelle());

                return $this->redirectToRoute('sortie_index');
            }
            if($sortie->getOrganisateur()->getId() == $user->getId()){
                $sortieRepository->remove($sortie, true);
                $this->addFlash('success', 'sortie Supprimée !');
            }
            else $this->addFlash('error', 'tentative non autorisée !');
        }
        else $this->addFlash('error', 'La sortie n\'existe pas');

        return $this->redirectToRoute('sortie_index');
    }

    #[Route('/cancel/{id}', name: 'cancel', requirements: ['id' => '\d+'])]
    public function cancel(Request $request, SortieRepository $sortieRepository, ParticipantRepository $participantRepository, EtatRepository $etatRepository, int $id): Response
    {
        $this->majEtatSorties($sortieRepository, $etatRepository);
        $etatAutorise = [2,3];

        $user = $this->getUserSession($request, $participantRepository);
        $sortie = $sortieRepository->find($id);

        if($sortie){
            if(!in_array($sortie->getEtat()->getId(), $etatAutorise)) {
                $this->addFlash('error', 'Action impossible sur une sortie '.$sortie->getEtat()->getLibelle());

                return $this->redirectToRoute('sortie_index');
            }
            if($sortie->getOrganisateur()->getId() == $user->getId()){
                $sortie->setEtat($etatRepository->find(6));
                //update des données
                $sortieRepository->save($sortie, true);
                $this->addFlash('success', 'sortie Annulée !');
            }
            else $this->addFlash('error', 'tentative non autorisée !');
        }
        else $this->addFlash('error', 'La sortie n\'existe pas');

        return $this->redirectToRoute('sortie_index');
    }

    #[Route('/publish/{id}', name: 'publish', requirements: ['id' => '\d+'])]
    public function publish(Request $request, SortieRepository $sortieRepository, ParticipantRepository $participantRepository, EtatRepository $etatRepository, int $id): Response
    {
        $this->majEtatSorties($sortieRepository, $etatRepository);
        $etatAutorise = [1];

        $user = $this->getUserSession($request, $participantRepository);
        $sortie = $sortieRepository->find($id);

        if($sortie){
            if(!in_array($sortie->getEtat()->getId(), $etatAutorise)) {
                $this->addFlash('error', 'Action impossible sur une sortie '.$sortie->getEtat()->getLibelle());

                return $this->redirectToRoute('sortie_index');
            }
            if($sortie->getOrganisateur()->getId() == $user->getId()){
                $sortie->setEtat($etatRepository->find(2));
                //update des données
                $sortieRepository->save($sortie, true);
                $this->addFlash('success', 'sortie Publiée !');
            }
            else $this->addFlash('error', 'tentative non autorisée !');
        }
        else $this->addFlash('error', 'La sortie n\'existe pas');

        return $this->redirectToRoute('sortie_index');
    }

    //retourne le user actuel
    private function getUserSession(Request $request,ParticipantRepository $participantRepository): Participant{
        return $participantRepository->findOneBy(['email' => $request->getSession()->get('_security.last_username')]);
    }

    //test si user est inscrit ou non
    private function isInscrit($user, $sortie): bool{
        foreach($sortie->getParticipants() as $participant){
            if($participant->getId() == $user->getId()) return true;
        }
        return false;
    }

    private function majEtatSorties(SortieRepository $sortieRepository, EtatRepository $etatRepository): void{
        $sorties = $sortieRepository->findAll();

        foreach ($sorties as $sortie){
            switch ($sortie->getEtat()->getId()){
                case 2:
                    if($sortie->getDateLimiteInscription() < new \DateTime()) $sortie->setEtat($etatRepository->find(3));
                    break;
                case 3:
                    if($sortie->getDateHeureDebut() < new \DateTime()) $sortie->setEtat($etatRepository->find(4));
                    break;
                case 4:
                    $timeStampFin = strtotime($sortie->getDateHeureDebut()->format('d-m-Y h:m:s'))+$sortie->getDuree();
                    $timeStampNow = strtotime((new \DateTime())->format('d-m-Y h:m:s'));

                    if($timeStampNow > $timeStampFin) $sortie->setEtat($etatRepository->find(5));
                    break;
            }
        }
    }
}
