<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Feed;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class AppFixtures extends Fixture
{
    private $encoder;
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    { 
        $sourceUrl = [
            'https://www.axelerant.com/tag/drupal-planet/feed',
            'http://feeds.bbci.co.uk/news/technology/rss.xml',
            'https://feeds.feedburner.com/symfony/blog',
        ];

        //load feeds
        foreach($sourceUrl as $url){
            $feedEntity = new Feed();
            $feedEntity->setUrl($url);
            $manager->persist($feedEntity);
        }

        //load users
        $user1 = new User();
        $password = $this->encoder->encodePassword($user1, 'testpass');
        $user1->setPassword($password)
            ->setUsername('aditya')
            ->setRoles(['ROLE_ADMIN']);
        $manager->persist($user1);

        $user2 = new User();
        $user2->setPassword($password)
            ->setUsername('user2')
            ->setRoles(['ROLE_USER']);
        $manager->persist($user2);

        $manager->flush();
    }
}
