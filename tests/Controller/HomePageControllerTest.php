<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomePageControllerTest extends WebTestCase
{

    private $client = null;

	protected function setUp(): void
	{
		$this->client = static::createClient();
	}

    public function testIndexNotAuthenticated()
    {
        $this->client->request('GET', '/');
        $this->assertResponseRedirects();
    }

    public function testIndexAuthenticated()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user1@gmail.com');
        $this->client->loginUser($testUser);
        $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }

}
