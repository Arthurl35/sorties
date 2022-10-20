<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Participant;

use App\Entity\Sortie;
use App\Repository\LieuRepository;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('nom', TextType::class, ['label' => 'nom de la sortie :'])
            ->add('dateHeureDebut',DateTimeType::class, [
                'label' => 'Date et heure de la sortie :',
                'html5' => true,
                'widget' => 'single_text'
            ])
            ->add('dateLimiteInscription',DateType::class, [
                'label' => 'Date limite d\'inscription :',
                'html5' => true,
                'widget' => 'single_text'
            ])
            ->add('nbInscriptionMax', NumberType::class, ['label' => 'Nombre de places :'])
            ->add('duree', NumberType::class, ['label' => 'Durée :'])
            ->add('infosSortie', TextType::class, ['label' => 'Description et infos :'])
            ->add('lieu', EntityType::class, [
                'label' => 'Lieu :',
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'query_builder' => function (LieuRepository $lieuRepository ){
                    return $lieuRepository->createQueryBuilder('s')->addGroupBy('s.nom');
                }
            ])
            ->add('add_lieu', ButtonType::class, [
                'attr' => ['id' => 'btn_add_lieu']
            ])
            ->add('nom_lieu', TextType::class, [
                'label' => 'Nom du lieu :',
                'required' => false,
                'attr' => [
                    'class' => 'f_lieu',
                    'display' => 'none'
                ]
            ])
            ->add('rue_lieu', TextType::class, [
                'label' => 'Rue :',
                'required' => false,
                'attr' => [
                    'class' => 'f_lieu',
                    'display' => 'none'
                ]
            ])
            ->add('ville_lieu', TextType::class, [
                'label' => 'Ville :',
                'required' => false,
                'attr' => [
                    'class' => 'f_lieu',
                    'display' => 'none'
                ]
            ])
            ->add('cp_lieu', TextType::class, [
                'label' => 'Code postal :',
                'required' => false,
                'attr' => [
                    'class' => 'f_lieu',
                    'display' => 'none'
                ]
            ])
            ->add('latitude_lieu', TextType::class, [
                'label' => 'Latitude :',
                'required' => false,
                'attr' => [
                    'class' => 'f_lieu',
                    'display' => 'none'
                ]
            ])
            ->add('longitude_lieu', TextType::class, [
                'label' => 'Longitude :',
                'attr' => [
                    'class' => 'f_lieu',
                    'display' => 'none'
                ]
            ])
            ->add('enregistrer', SubmitType::class)
            ->add('publier', SubmitType::class)
            ->add('annuler', SubmitType::class)



        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
