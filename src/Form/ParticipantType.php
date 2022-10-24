<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class,[
                'label' => 'Adresse e-mail :',
                'attr' => ['class' => '']
            ])
            ->add('password', PasswordType::class,[
                'invalid_message' => 'Les mots de passes ne sont pas identiques.',
                'mapped' => false,
                'label' => 'Mot de passe :',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci d\'entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit comporter au minimum {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                    ],
            ])
            ->add('nom', TextType::class,[
                'label' => 'Nom :',
                'attr' => ['class' => '']
            ])
            ->add('prenom', TextType::class,[
                'label' => 'Prénom :',
                'attr' => ['class' => '']
            ])
            ->add('telephone', TextType::class,[
                'label' => 'N° de téléphone :',
                'attr' => ['class' => '']
            ])
            ->add('administrateur', CheckboxType::class,[
                'required' => false,
                'label' => 'Administrateur :',
                'attr' => ['class' => '']
            ])
            ->add('actif', CheckboxType::class,[
                'required' => false,
                'label' => 'Actif :',
                'attr' => ['class' => '']
            ])
            ->add('backdrop', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Photo de profil :',
                'constraints' => [
                    new Image([
                        'maxSize' => '7000k',
                        'maxSizeMessage' => 'The file is too big !'
                    ])
                ]
            ])
            ->add('site', EntityType::class, [
                'label' => 'Site de rattachement :',
                'class' => Site::class,
                'choice_label' => 'nom',
                'query_builder' => function (SiteRepository $siteRepository ){
                    return $siteRepository->createQueryBuilder('s')->addGroupBy('s.nom');
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
