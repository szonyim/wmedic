<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->
            setUserName("WebMedic Teszt")
            ->setEmail("teszt@webmedic.hu")
            ->setPhoneNumber("+3630/111-2222")
            ->setPassword($this->passwordEncoder->encodePassword($user, 'wm_pass'));

        $manager->persist($user);

        $manager->flush();
    }
}
