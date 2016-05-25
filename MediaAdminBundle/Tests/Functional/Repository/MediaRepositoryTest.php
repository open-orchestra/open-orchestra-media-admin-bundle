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
    protected $keywordsLabelToId;

    /**
     * Set up test
     */
    protected function setUp()
    {
        parent::setUp();

        static::bootKernel();
        $keywordRepository = static::$kernel->getContainer()->get('open_orchestra_model.repository.keyword');
        $keywords = $keywordRepository->findAll();
        $this->keywordsLabelToId = array();
        foreach($keywords as $keywords) {
            $this->keywordsLabelToId[$keywords->getLabel()] = $keywords->getId();
        }
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
        $keywords = implode(',', $this->replaceKeywordLabelById(explode(',', $keywords)));
        $keywords = $this->repository->findByKeywords($keywords);

        $this->assertCount($count, $keywords);
    }

    /**
     * @return array
     */
    public function provideKeywordAndCount()
    {
        return array(
            array('lorem', 5),
            array('sit', 0),
            array('dolor', 4),
            array('lorem,dolor', 5),
        );
    }

    /**
     * @param array $data
     */
    protected function replaceKeywordLabelById($data)
    {
        $keywordsLabelToId = $this->keywordsLabelToId;
        array_walk_recursive($data, function (&$item, $key) use ($keywordsLabelToId) {
            if (is_string($item)) {
                $item = str_replace(array_keys($keywordsLabelToId), $keywordsLabelToId, $item);
            }
        });
        return $data;
    }
}
