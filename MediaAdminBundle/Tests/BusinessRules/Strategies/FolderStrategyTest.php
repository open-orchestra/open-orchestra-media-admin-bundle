<?php
namespace OpenOrchestra\BackOffice\Tests\BusinessRules\Strategies;

use OpenOrchestra\Backoffice\BusinessRules\Strategies\BusinessActionInterface;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use OpenOrchestra\Media\Model\MediaFolderInterface;
use Phake;
use OpenOrchestra\MediaAdminBundle\BusinessRules\Strategies\FolderStrategy;

/**
 * Class FolderStrategyTest
 */
class FolderStrategyTest extends AbstractBaseTestCase
{
    protected $folderManager;
    protected $strategy;

    /**
     * setUp
     */
    public function setUp()
    {
        $this->folderManager = Phake::mock('OpenOrchestra\MediaAdminBundle\Manager\FolderManager');

        $this->strategy = new FolderStrategy($this->folderManager);
    }

    /**
     * @param boolean $isDeletable
     * @param boolean $isGranted
     *
     * @dataProvider provideDeletable
     */
    public function testCanDelete($isDeletable, $isGranted)
    {
        Phake::when($this->folderManager)->isDeletable(Phake::anyParameters())->thenReturn($isDeletable);
        $this->assertSame($isGranted, $this->strategy->canDelete(Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface'), array()));
    }

    /**
     * provide folder isDeletable
     *
     * @return array
     */
    public function provideDeletable()
    {
        return array(
            array(true, true),
            array(false, false),
        );
    }

    /**
     * test getActions
     */
    public function testGetActions()
    {
        $this->assertEquals(array(
            BusinessActionInterface::DELETE => 'canDelete',
        ), $this->strategy->getActions());
    }

    /**
     * test getActions
     */
    public function testType()
    {
        $this->assertEquals(MediaFolderInterface::ENTITY_TYPE, $this->strategy->getType());
    }
}
