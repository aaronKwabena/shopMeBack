<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\Panier;
use App\Entity\UserProfile;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }
    
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Admin user
        $userAdmin= new User();
        $userAdmin->setFullName('admin');
        $userAdmin->setEmail('admin@shopme.fr');
        $userAdmin->setRoles(['ROLE_ADMIN']);
        $userAdmin->setPassword('kokomlemle');
        $manager->persist($userAdmin);

        $hashedPassword = $this->userPasswordHasher->hashPassword($userAdmin, 'kokomlemle');
        $userAdmin->setPassword($hashedPassword);

        $manager->persist($userAdmin);

        // Regular users
        for ($i = 0; $i < 9; $i++) {
            $user = new User();
            $user->setFullName($faker->name)
                ->setEmail("user$i@domain.fr")
                ->setPassword('12345678')
                ->setRoles(['ROLE_USER']);

        $hashedPassword = $this->userPasswordHasher->hashPassword($user, '12345678');
        $user->setPassword($hashedPassword);

                // Create a user profile and associate it with the user
            $userProfile = new UserProfile();
            $userProfile->setPhone($faker->phoneNumber)
                ->setAddress($faker->streetAddress)
                ->setCodePostal($faker->postcode)
                ->setCity($faker->city)
                ->setCountry($faker->country);

            // Associate the user profile with the user
            $user->setUserProfile($userProfile);

            $manager->persist($user);
            if (!$manager->contains($userProfile)) {
                $manager->persist($userProfile);
            }
        }

        $manager->flush();
    }
}

