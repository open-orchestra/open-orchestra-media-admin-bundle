<?php

namespace OpenOrchestra\Backoffice\Tests\Perimeter\Strategy;

use OpenOrchestra\MediaAdmin\Perimeter\Strategy\MediaFolderPerimeterStrategy;
use OpenOrchestra\Media\Model\MediaFolderInterface;
use Phake;

/**
 * Class MediaFolderPerimeterStrategyTest
 */
class MediaFolderPerimeterStrategyTest extends AbstractPerimeterStrategyTest
{
    /**
     * set up the test
     */
    public function setUp()
    {
        $this->strategy = new MediaFolderPerimeterStrategy();
        $this->type = MediaFolderInterface::ENTITY_TYPE;
    }

    /**
     * Provide perimeters
     */
    public function providePerimeters()
    {
        $path = '/root/folder1/folder2/';

        return array(
            'Bad perimeter type : Node'         => array($path, $this->createPhakeNodePerimeter(), false),
            'Bad perimeter type : Content type' => array($path, $this->createPhakeContentTypePerimeter(), false),
            'Bad perimeter type : Site'         => array($path, $this->createPhakeSitePerimeter(), false),
            'Bad item : Site id'                => array(2 , $this->createPhakeMediaFolderPerimeter(), false),
            'Not in perimeter'                  => array('/root/folder3/folder4', $this->createPhakeMediaFolderPerimeter(), false),
            'In perimeter'                      => array($path, $this->createPhakeMediaFolderPerimeter(), true),
        );
    }

    /**
     * Create a phake MediaFolderPerimeter
     *
     * @return PerimeterInterface
     */
    protected function createPhakeMediaFolderPerimeter()
    {
        $perimeter = Phake::mock('OpenOrchestra\Backoffice\Model\PerimeterInterface');
        Phake::when($perimeter)->getType()->thenReturn(MediaFolderInterface::ENTITY_TYPE);
        $items = array('/root/folder1/', '/root/folder2/');
        Phake::when($perimeter)->getItems()->thenReturn($items);

        return $perimeter;
    }
}
