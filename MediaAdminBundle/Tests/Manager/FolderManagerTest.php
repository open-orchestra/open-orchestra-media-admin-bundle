<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Manager;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use OpenOrchestra\Media\Model\MediaFolderInterface;
use OpenOrchestra\MediaAdminBundle\Manager\FolderManager;
use Doctrine\Common\Collections\ArrayCollection;

use Phake;

/**
 * Class FolderManagerTest
 */
class FolderManagerTest extends AbstractBaseTestCase
{
    protected $manager;
    protected $documentManager;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->documentManager = Phake::mock('Doctrine\ODM\MongoDB\DocumentManager');

        $this->manager = new FolderManager($this->documentManager);
    }

    /**
     * @param MediaFolderInterface $folder
     * @param int                  $expectedCall
     * @param boolean              $isDeletable
     *
     * @dataProvider provideFolder
     */
    public function testDeleteTree($folder, $expectedCall, $isDeletable)
    {
        $this->manager->deleteTree($folder);
        Phake::verify($this->documentManager, Phake::times($expectedCall))->remove($folder);
    }

    /**
     * @param MediaFolderInterface $folder
     * @param int                  $expectedCall
     * @param boolean              $isDeletable
     *
     * @dataProvider provideFolder
     */
    public function testIsDeletable($folder, $expectedCall, $isDeletable)
    {
        $this->assertEquals($isDeletable, $this->manager->isDeletable($folder));
    }

    /**
     * @return array
     */
    public function provideFolder()
    {
        $subfolder0 = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        Phake::when($subfolder0)->getMedias()->thenReturn(new ArrayCollection());
        Phake::when($subfolder0)->getSubFolders()->thenReturn(new ArrayCollection());

        $subfolder1 = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        Phake::when($subfolder1)->getMedias()->thenReturn(new ArrayCollection());
        Phake::when($subfolder1)->getSubFolders()->thenReturn(new ArrayCollection());

        $medias = new ArrayCollection();
        $medias->add(Phake::mock('OpenOrchestra\Media\Model\MediaInterface'));
        $medias->add(Phake::mock('OpenOrchestra\Media\Model\MediaInterface'));

        $subfolders = new ArrayCollection();
        $subfolders->add($subfolder0);
        $subfolders->add($subfolder1);

        $folder0 = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        Phake::when($folder0)->getMedias()->thenReturn($medias);
        Phake::when($folder0)->getSubFolders()->thenReturn($subfolders);

        $folder1 = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        Phake::when($folder1)->getMedias()->thenReturn(new ArrayCollection());
        Phake::when($folder1)->getSubFolders()->thenReturn(new ArrayCollection());

        return array(
            array($folder0, 0, false),
            array($folder1, 1, true),
        );
    }
}
