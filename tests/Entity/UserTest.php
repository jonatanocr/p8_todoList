<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserTest extends KernelTestCase
{

    private $user;
    private $passwordHasher;

    public function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $this->user = new User();
    }

    public function testId()
    {
        $this->assertNull($this->user->getId());
    }

    public function testEmail()
    {
        $userEmail = "email@domaine.com";
        $this->user->setEmail($userEmail);
        $this->assertEquals($userEmail, $this->user->getEmail());
    }

    public function testUserIdentifier()
    {
        $email = "email@domaine.com";
        $this->user->setEmail($email);
        $this->assertEquals($email, $this->user->getUserIdentifier());
    }

    public function testRoles()
    {
        $roles = ["ROLE_ADMIN"];
        $this->user->setRoles($roles);
        $this->assertContains($roles[0], $this->user->getRoles());
        $this->assertContains("ROLE_USER", $this->user->getRoles());
    }

    public function testPassword()
    {
        $userPassword = "User_Secure-P@ssw0rd";
        $passwordHashed = $this->passwordHasher->hashPassword($this->user, $userPassword);
        $this->user->setPassword($passwordHashed);
        $this->assertTrue($this->passwordHasher->isPasswordValid($this->user, $userPassword));
        $this->assertEquals($passwordHashed, $this->user->getPassword());
    }

    public function testUsername()
    {
        $username = "username1";
        $this->user->setUsername($username);
        $this->assertEquals($username, $this->user->getUsername());
    }

    public function testUserAddTask()
    {
        $task = new Task();
        $this->user->addTask($task);
        $this->assertEquals($task, $this->user->getTasks()[0]);
    }

    public function testUserRemoveTask()
    {
        $task = new Task();
        $this->user->addTask($task);
        $this->user->removeTask($task);
        $this->assertCount(0, $this->user->getTasks());
    }

    
}