<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Functional\Controller;

use OpenOrchestra\BackofficeBundle\Tests\Functional\Controller\AbstractControllerTest;

/**
 * Class FolderControllerTest
 *
 * @group media
 */
class FolderControllerTest extends AbstractControllerTest
{
    /**
     * Set up the test
     */
    public function setUp()
    {
    }

    /**
     * @param string $username
     * @param string $password
     */
    public function connect($username, $password)
    {
        $this->client = static::createClient();
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Log in')->form();
        $form['_username'] = $username;
        $form['_password'] = $password;

        $this->client->submit($form);
    }

    /**
     * Test folder form
     */
    public function testMediaFolderFormAdmin()
    {
        $this->connect("admin", "admin");

        $crawler = $this->getCrawler();
        $this->assertForm($this->client->getResponse());

        $form = $crawler->selectButton('Save')->form();

        $this->client->submit($form);
        $this->assertForm($this->client->getResponse());
    }
    
    public function testMediaFolderFormUserWithCreateRole()
    {
        $this->connect("userFolderCreate", "userFolderCreate");

        $crawler = $this->getCrawler();
        $response = $this->client->getResponse();

        $form = $crawler->selectButton('Save')->form();
        $this->client->submit($form);
        $newResponse = $this->client->getResponse();

        $this->assertSame($response, $newResponse);
    }

    protected function getCrawler()
    {
        $mediaFolderRepository = static::$kernel->getContainer()->get('open_orchestra_media.repository.media_folder');
        $mediaFolder = $mediaFolderRepository->findOneByName('Images folder');

        $url = '/admin/folder/form/' . $mediaFolder->getId();
        $crawler = $this->client->request('GET', $url);

        return $crawler;
    }
}
