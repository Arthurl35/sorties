<?php

namespace App\Form;

use App\Entity\Lieu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label'=> 'Nom :',
                'attr' => ['class' => '']
            ])
            ->add('rue', TextType::class, [
                'label'=> 'Rue :',
                'attr' => ['class' => '']
            ])
            ->add('latitude', IntegerType::class, [
                'label'=> 'Latitude :',
                'attr' => ['class' => '']
            ])
            ->add('longitude', IntegerType::class, [
                'label'=> 'Longitude :',
                'attr' => ['class' => '']
            ])
            ->add('ville', TextType::class, [
                'label'=> 'Ville :',
                'attr' => ['class' => '']
            ])
            ->add('cp', TextType::class, [
                'label'=> 'Code postal :',
                'attr' => ['class' => '']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
