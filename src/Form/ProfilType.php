<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, [
                'label' => 'prénom :',
                'attr' => ['class' => '']
            ])
            ->add('nom', TextType::class, [
                'label' => 'nom :',
                'attr' => ['class' => '']
            ])
            ->add('telephone', TextType::class, [
                'label' => 'telephone :',
                'attr' => ['class' => '']
            ])
            ->add('email', EmailType::class, [
                'label' => 'email :',
                'attr' => ['class' => '']
            ])
            ->add('password', RepeatedType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'required' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'first_options'  => ['label' => 'mot de passe :'],
                'second_options' => ['label' => 'répéter mot de passe :'],
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),

                ],

            ])
            ->add('site', EntityType::class, [
                'label' => 'site :',
                'class' => Site::class,
                'choice_label' => 'nom',
                'query_builder' => function (SiteRepository $siteRepository ){
                return $siteRepository->createQueryBuilder('s')->addGroupBy('s.nom');
                }
            ])
            ->add('backdrop', FileType::class, [
                'label' => 'photo de profil :',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '7000k',
                        'maxSizeMessage' => 'The file is too big !'
                    ])
                ]
            ])
            ->add('modifier', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
            "required" => false
        ]);
    }
}
