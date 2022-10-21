<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\Sortie;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', SearchType::class, ['label' => 'Le nom de la sortie contient :'])
            ->add('dateHeureDebut',DateTimeType::class, [
                'label' => 'Entre',
                'html5' => true,
                'widget' => 'single_text'
            ])
            ->add('dateLimiteInscription',DateType::class, [
                'label' => 'et',
                'html5' => true,
                'widget' => 'single_text'
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
                'query_builder' => function (SiteRepository $siteRepository ){
                    return $siteRepository->createQueryBuilder('s')->addGroupBy('s.nom');
                }
            ])
            ->add('organisateur')
            ->add('participants')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
