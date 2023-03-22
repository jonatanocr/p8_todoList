<?php

namespace App\Tests\Entity;

use \DateTimeImmutable;
use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{

    private $task;

    public function setUp(): void
    {
        $this->task = new Task();
    }

    public function testId()
    {
        $this->assertNull($this->task->getId());
    }

    public function testCreatedAt()
    {
        $createdAt = new DateTimeImmutable();

        $this->task->setCreatedAt($createdAt);
        $this->assertEquals($createdAt, $this->task->getCreatedAt());
    }

    public function testUpdatedAt()
    {
        $updatedAt = new DateTimeImmutable();

        $this->task->setUpdatedAt($updatedAt);
        $this->assertEquals($updatedAt, $this->task->getUpdatedAt());
    }

    public function testTitle()
    {
        $taskTitle = "Some task title";

        $this->task->setTitle($taskTitle);
        $this->assertEquals($taskTitle, $this->task->getTitle());
    }

    public function testContent()
    {
        $taskContent = "Some task content";

        $this->task->setContent($taskContent);
        $this->assertEquals($taskContent, $this->task->getContent());
    }

    public function testIsDone()
    {
        $taskStatus = true;

        $this->task->setIsDone($taskStatus);
        $this->assertEquals($taskStatus, $this->task->isIsDone());
    }

    public function testAuthor()
    {
        $user = new User();

        $this->task->setAuthor($user);
        $this->assertEquals($user, $this->task->getAuthor());
    }

}