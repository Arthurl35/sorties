<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $userPasswordHasher;
    private Generator $generator;
    private ObjectManager $manager;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->generator = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $this->manager = $manager;

        $this->addUsers();
    }

    public function addUsers(){

        for($i = 0; $i < 10; $i++){

            $Participant = new Participant();
            $Participant->setRoles(['ROLE_USER'])
                ->setEmail($this->generator->email)
                ->setPrenom($this->generator->firstName)
                ->setNom($this->generator->lastName)
                ->setTelephone($this->generator->phoneNumber)
                ->setAdministrateur($this->generator->numberBetween(0,1))
                ->setActif($this->generator->numberBetween(0,1))
                ->setPassword($this->userPasswordHasher->hashPassword($Participant, '123456'));

            $this->manager->persist($Participant);
        }

        $this->manager->flush();

    }
}
