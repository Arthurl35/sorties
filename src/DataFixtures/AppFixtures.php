<?php
namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
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
        $this->manager = $manager;
        $this->addUsers();
        $this->addUsersAdmin();

    }
    public function addUsersAdmin(){
        $sites = ['Nantes','Rennes','Quimper','Niort'];
        for($i = 0; $i < 1; $i++){
            $Participant = new Participant();
            $site = new Site();
            $site->setNom($this->generator->randomElement($sites));
            $Participant->setRoles(['ROLE_ADMIN'])
                ->setBackdrop($this->generator->word . ".png")
                ->setEmail($this->generator->email)
                ->setPrenom($this->generator->firstName)
                ->setNom($this->generator->lastName)
                ->setTelephone($this->generator->phoneNumber)
                ->setAdministrateur($this->generator->numberBetween(0,1))
                ->setActif($this->generator->numberBetween(0,1))
                ->setPassword($this->userPasswordHasher->hashPassword($Participant, '123456'))
                ->setSite($site);

            $this->manager->persist($Participant);
            $this->manager->persist($site);
        }
        $this->manager->flush();
    }
    public function addUsers(){
        $sites = ['Nantes','Rennes','Quimper','Niort'];
        for($i = 0; $i < 1; $i++){
            $Participant = new Participant();
            $site = new Site();
            $site->setNom($this->generator->randomElement($sites));
            $Participant->setRoles(['ROLE_ADMIN'])
                ->setBackdrop($this->generator->word . ".png")
                ->setEmail($this->generator->email)
                ->setPrenom($this->generator->firstName)
                ->setNom($this->generator->lastName)
                ->setTelephone($this->generator->phoneNumber)
                ->setAdministrateur($this->generator->numberBetween(0,1))
                ->setActif($this->generator->numberBetween(0,1))
                ->setPassword($this->userPasswordHasher->hashPassword($Participant, '123456'))
                ->setSite($site);

            $this->manager->persist($Participant);
            $this->manager->persist($site);
        }
        $this->manager->flush();
    }


}
