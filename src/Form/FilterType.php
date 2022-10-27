<?php

namespace App\Form;

use App\Entity\Filter;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('site', EntityType::class, [
                'label' => 'Site : ',
                'class' => Site::class,
                'choice_label' => 'nom',
                'query_builder' => function (SiteRepository $siteRepository ){
                    return $siteRepository->createQueryBuilder('s')->addGroupBy('s.id');
                }
            ])
            ->add('nom', SearchType::class, [
                'label' => 'Le nom de la sortie contient :',
                'required' => false,
            ])
            ->add('dateHeureDebut',DateType::class, [
                'label' => 'Entre',
                'required' => false,
                'html5' => true,
                'widget' => 'single_text',
                'by_reference' => true,
            ])
            ->add('dateHeureFin',DateType::class, [
                'label' => 'et',
                'required' => false,
                'html5' => true,
                'widget' => 'single_text',
                'by_reference' => true,
            ])
            ->add('sortieOrganisateur', CheckboxType::class, [
                'label'    => 'Sorties dont je suis l\'organisateur',
                'required' => false,
            ])
            ->add('sortieInscrit', CheckboxType::class, [
                'label'    => 'Sorties auxquelles je suis inscrit(e)',
                'required' => false,
            ])
            ->add('sortiePasInscrit', CheckboxType::class, [
                'label'    => 'Sorties auxquelles je ne suis pas inscrit(e)',
                'required' => false,
            ])
            ->add('sortiePasse', CheckboxType::class, [
                'label'    => 'Sorties passées',
                'required' => false,
            ])
            ->add('rechercher', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Filter::class,
        ]);
    }
}
