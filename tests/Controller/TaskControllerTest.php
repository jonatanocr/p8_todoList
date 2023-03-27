<?php

namespace App\Tests\Controller;

use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{

    private $client = null;

	protected function setUp(): void
	{
		$this->client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user1@gmail.com');
        $this->client->loginUser($testUser);
	}

    public function testCreateTaskRenderForm()
    {
        $crawler = $this->client->request('GET', '/task/create');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer une tâche');
    }

    public function testCreateTaskSubmitForm()
    {
        $crawler = $this->client->request('GET', '/task/create');
        $form = $crawler->selectButton('Ajouter')->form();
        $this->client->submit($form, [
            'task_form[title]'    => 'Test new task title',
            'task_form[content]' => 'Test new task Content',
        ]);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
        $this->assertSelectorTextContains('.alert-success', 'La tâche a bien été ajoutée.');
    }

    public function testListTask()
    {
        $crawler = $this->client->request('GET', '/task/list');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Liste des tâches');
    }

    public function testUpdateTaskRenderForm() 
    {
        $taskRepository = static::getContainer()->get(TaskRepository::class);
        $testTask = $taskRepository->findOneByTitle('Title 1');
        $crawler = $this->client->request('GET', '/task/update/'.$testTask->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifier une tâche');
    }

    public function testUpdateTaskSubmitForm() 
    {
        $taskRepository = static::getContainer()->get(TaskRepository::class);
        $testTask = $taskRepository->findOneByTitle('Title 1');
        $crawler = $this->client->request('GET', '/task/update/'.$testTask->getId());
        $form = $crawler->selectButton('Modifier')->form();
        $this->client->submit($form, [
            'task_form[title]'    => 'Title 1 Updated',
            'task_form[content]' => 'Content task 1 Updated',
        ]);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'La tâche a bien été mise à jour');
    }

    public function testUpdateStatusAsDone()
    {
        $taskRepository = static::getContainer()->get(TaskRepository::class);
        $testTask = $taskRepository->findOneByTitle('Title 1');
        $crawler = $this->client->request('GET', '/task/status/'.$testTask->getId().'/1');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'La tâche Title 1 a bien été marquée comme faite.');
    }

    public function testUpdateStatusAsToDo()
    {
        $taskRepository = static::getContainer()->get(TaskRepository::class);
        $testTask = $taskRepository->findOneByTitle('Title 5');
        $crawler = $this->client->request('GET', '/task/status/'.$testTask->getId().'/0');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'La tâche Title 5 a bien été marquée comme à faire.');
    }

    public function testDeleteTask()
    {
        $taskRepository = static::getContainer()->get(TaskRepository::class);
        $testTask = $taskRepository->findOneByTitle('Title 1');
        $crawler = $this->client->request('GET', '/task/delete/'.$testTask->getId());
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'La tâche a bien été supprimée.');
    }

}
