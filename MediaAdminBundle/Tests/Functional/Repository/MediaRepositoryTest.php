<?php

namespace OpenOrchestra\MediaBundle\Tests\Functional\Repository;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractKernelTestCase;
use OpenOrchestra\Media\Repository\MediaRepositoryInterface;

/**
 * Class MediaRepositoryTest
 *
 * @group integrationTest
 */
class MediaRepositoryTest extends AbstractKernelTestCase
{
    /**
     * @var MediaRepositoryInterface
     */
    protected $repository;

    /**
     * Set up test
     */
    protected function setUp()
    {
        parent::setUp();

        static::bootKernel();
        $this->repository = static::$kernel->getContainer()->get('open_orchestra_media.repository.media');
    }

    /**
     * @param string $keywords
     * @param int    $count
     *
     * @dataProvider provideKeywordAndCount
     */
    public function testFindByKeywords($keywords, $count)
    {
        $keywords = $this->repository->findByKeywords($keywords);

        $this->assertCount($count, $keywords);
    }

    /**
     * @return array
     */
    public function provideKeywordAndCount()
    {
        return array(
            array('Lorem', 5),
            array('Sit', 0),
            array('Dolor', 4),
            array('Lorem,Dolor', 5),
        );
    }
}
