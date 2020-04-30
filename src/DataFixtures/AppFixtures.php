<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Picture;
use App\Entity\Trick;
use App\Entity\User;
use App\Entity\Video;
use Cocur\Slugify\Slugify;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var Generator
     */
    protected $faker;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr-FR');
        $slugify = new Slugify();

        $users = [];
        $categories = [];
        $categoryNameArray = ['Grab', 'Rotation', 'Flip', 'Rotation désaxée', 'Slide', 'One foot', 'Old school'];
        $tricksNameArray = [
            'Mute', 'Sad', 'Indy', 'Stalefish', 'Tail grab', 'Nose grab', 'Japan', 'Seat belt', 'Truck driver',
            '360', '900', 'Rodeo', 'Misty', 'Nose slide', 'Backside air', 'Method air'
        ];
        $picturesNameArray = ['default_1', 'default_2', 'default_3'];


        // 10 Utilisateurs
        for ($u = 0; $u < 10; $u++) {
            $user = new User();

            $user
                ->setUsername($faker->userName)
                ->setEmail($faker->unique()->safeEmail)
                ->setDate($faker
                    ->dateTimeBetween($startDate = '-6 months', $endDate = 'now', $timezone = 'Europe/Paris'))
                ->setPassword($this->encoder->encodePassword($user, 'password'))
                ->setPictureName("default.png")
                ->setProfilPicturePath('uploads/profil')
                ->setToken(bin2hex(random_bytes(64)))
                ->setActivated(true)
                ->setRoles(["ROLE_USER"]);

            $users[] = $user;

            $manager->persist($user);
        }

        // 7 Catégories
        foreach ($categoryNameArray as $categoryName) {
            $category = new Category();
            $category->setName($categoryName);
            $manager->persist($category);

            $categories[] = $category;
        }

        // 16 Figures
        foreach ($tricksNameArray as $trickName) {
            $trick = new Trick();

            $trick
                ->setName($trickName)
                ->setDescription($faker->paragraph(12))
                ->setDate($faker
                    ->dateTimeBetween($startDate = '-12 months', $endDate = '-6 months', $timezone = 'Europe/Paris'))
                ->setUpdateDate($faker
                    ->dateTimeBetween($startDate = '-3 months', $endDate = 'now', $timezone = 'Europe/Paris'))
                ->setSlug($slugify->slugify($trick->getName()))
                ->setUser($faker->randomElement($users))
                ->setCategory($faker->randomElement($categories));

            $manager->persist($trick);

            // 3 Images par figure
            foreach ($picturesNameArray as $pictureName) {
                $picture = new Picture();

                $picture
                    ->setName($pictureName . '.jpg')
                    ->setPath('uploads/trick')
                    ->setTrick($trick);
                $manager->persist($picture);

                // La dernière image devient l'image principale
                $trick->setMainPicture($picture);
                $manager->persist($trick);
            }

            // 1 à 2 vidéos par figure
            for ($v = 0; $v < mt_rand(1, 2); $v++) {
                $video = new Video();

                $video
                    ->setUrl('https://www.youtube.com/embed/n0F6hSpxaFc')
                    ->setTrick($trick);

                $manager->persist($video);
            }

            // 0 à 30 commentaires par figures
            for ($c = 0; $c < mt_rand(0, 30); $c++) {
                $comment = new Comment();

                $comment
                    ->setContent($faker->sentence(mt_rand(1, 5)))
                    ->setDate($faker
                        ->dateTimeBetween($startDate = '-2 months', $endDate = 'now', $timezone = 'Europe/Paris'))
                    ->setUser($faker->randomElement($users))
                    ->setTrick($trick);

                $manager->persist($comment);
            }
        }

        $manager->flush();
    }
}
