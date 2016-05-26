<?php

namespace OpenOrchestra\BackofficeBundle\Tests\Functional\EventFactory;

use OpenOrchestra\BackofficeBundle\Tests\Functional\AbstractAuthentificatedTest;
use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\ConfigurableContentStrategy;
use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\ContentListStrategy;
use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\VideoStrategy;
use OpenOrchestra\Media\DisplayBlock\Strategies\GalleryStrategy;
use OpenOrchestra\ModelBundle\Document\Block;
use OpenOrchestra\ModelInterface\Model\BlockInterface;
use Symfony\Component\Form\FormFactoryInterface;
use OpenOrchestra\ModelInterface\Repository\ReadContentRepositoryInterface;
use OpenOrchestra\MediaModelBundle\Document\MediaFolder;

/**
 * Class FolderEventFactoryTest
 *
 * @group backofficeTest
 */
class FolderEventFactoryTest extends AbstractAuthentificatedTest
{
    /**
     * @var
     */
    protected $folderEvent;
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;
    protected $folder;

    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();
        $this->folderEvent = static::$kernel->getContainer()->get('open_orchestra_media_admin.event.folder_event');

        $this->folder = new MediaFolder();
        $this->folderEvent->setFolder($this->folder);
    }

    /**
     * test instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf('OpenOrchestra\MediaAdmin\Event\FolderEvent', $this->folderEvent);
    }

    /**
     * test instance
     */
    public function testGetFolder()
    {
        $this->assertSame($this->folder, $this->folderEvent->getFolder());
    }
}
