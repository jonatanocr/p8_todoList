<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use \DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher, ManagerRegistry $doctrine)
    {
        $this->hasher = $hasher;
        $this->doctrine = $doctrine;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) {
            ${"user$i"} = new User();
            if ($i === 0) {
                ${"user$i"}->setEmail('anonyme@gmail.com');
                ${"user$i"}->setUsername('anonyme');
            } else {
                ${"user$i"}->setEmail('user' . $i . '@gmail.com');
                ${"user$i"}->setUsername('user' . $i);
            }
            ${"user$i"}->setPassword(
                $this->hasher->hashPassword(
                    ${"user$i"}, 'password'
                )
            );
            if ($i < 3) {
                ${"user$i"}->setRoles(['ROLE_ADMIN']);
            } else {
                ${"user$i"}->setRoles(['ROLE_USER']);
            }
            $manager->persist( ${"user$i"});
        }

        $now = new DateTimeImmutable();
        for ($i = 1; $i < 10; $i++) {
            $randUser = rand(0, 9);
            $author = ${"user$randUser"};
            $task = new Task();
            $task->setCreatedAt($now);
            $task->setUpdatedAt($now);
            $task->setTitle('Title ' . $i);
            $task->setContent('Content task ' . $i);
            $task->setAuthor($author);
            if ($i < 5) {
                $task->setIsDone(false);
            } else {
                $task->setIsDone(true);
            }
            $manager->persist($task);
        }
        $manager->flush();

    }
}
