<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private $client = null;

	protected function setUp(): void
	{
		$this->client = static::createClient();
	}

    private function loginUser($role = null)
    {
        $userEmail = '';
        if ($role === 'ROLE_ADMIN') {
            $userEmail = 'user1@gmail.com';
        } elseif ($role === 'ROLE_USER') {
            $userEmail = 'user3@gmail.com';
        }
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail($userEmail);
        $this->client->loginUser($testUser);
        return $testUser;
    }

    public function testRegisterUserRenderForm()
    {
        $crawler = $this->client->request('GET', '/register');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer un utilisateur');
    }

    public function testRegisterUserSubmitForm()
    {
        $crawler = $this->client->request('GET', '/register');
        $form = $crawler->selectButton('Ajouter')->form();
        $this->client->submit($form, [
            'registration_form[username]'    => 'TestUser',
            'registration_form[email]' => 'test_user@gmail.com',
            'registration_form[plainPassword][first]' => 'Some-5ecure_P@ssword',
            'registration_form[plainPassword][second]' => 'Some-5ecure_P@ssword',
        ]);

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        // home redirection
        $this->client->followRedirect();
        // auto reditection from home to login page if user is not logged
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
        $this->assertSelectorTextContains('.alert-success', 'Le compte utilisateur est créé.');

    }

    public function testUpdateUserRenderForm()
    {
        $this->loginUser('ROLE_USER');
        $crawler = $this->client->request('GET', '/user/update');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifier user3');
    }

    public function testUpdateUserSubmitForm()
    {
        $this->loginUser('ROLE_USER');
        $crawler = $this->client->request('GET', '/user/update');
        $form = $crawler->selectButton('Modifier')->form();
        $this->client->submit($form, [
            'update_user_form[username]'    => 'User3',
            'update_user_form[email]' => 'user3@gmail.com',
            'update_user_form[plainPassword][first]' => 'Some-5ecure_P@ssword',
            'update_user_form[plainPassword][second]' => 'Some-5ecure_P@ssword',
        ]);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
        $this->assertSelectorTextContains('.alert-success', 'Le compte utilisateur est modifié.');
    }

    public function testUpdateUserAsAdminRenderForm()
    {
        $this->loginUser('ROLE_ADMIN');
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userToUpdate = $userRepository->findOneByEmail('user3@gmail.com');
        $crawler = $this->client->request('GET', '/user/admin_update/' . $userToUpdate->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifier user3');
    }

    public function testUpdateUserAsAdminSubmitForm()
    {
        $this->loginUser('ROLE_ADMIN');
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userToUpdate = $userRepository->findOneByEmail('user3@gmail.com');
        $crawler = $this->client->request('GET', '/user/admin_update/' . $userToUpdate->getId());
        $form = $crawler->selectButton('Modifier')->form();
        $this->client->submit($form, [
            'update_user_form[username]'    => 'User3',
            'update_user_form[email]' => 'user3@gmail.com',
            'update_user_form[roles]' => 'ROLE_ADMIN',
        ]);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
        $this->assertSelectorTextContains('.alert-success', 'Le compte utilisateur est modifié.');
    }

    public function testDeleteUser()
    {
        $this->loginUser('ROLE_ADMIN');
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userToDelete = $userRepository->findOneByEmail('user3@gmail.com');
        $crawler = $this->client->request('GET', '/user/delete/' . $userToDelete->getId());
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
        $this->assertSelectorTextContains('.alert-success', 'Le compte utilisateur est supprimé.');
    }

    public function testDeleteUserSelf()
    {
        $userToDelete = $this->loginUser('ROLE_USER');
        $crawler = $this->client->request('GET', '/user/delete/' . $userToDelete->getId());
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->client->followRedirect();
    }

    public function testListUser()
    {
        $this->loginUser('ROLE_ADMIN');
        $crawler = $this->client->request('GET', '/user/list');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');  
    }
}