<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Manager;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use OpenOrchestra\Media\Model\MediaFolderInterface;
use OpenOrchestra\Media\Repository\MediaRepositoryInterface;
use OpenOrchestra\MediaAdminBundle\Manager\FolderManager;
use Doctrine\Common\Collections\ArrayCollection;

use Phake;

/**
 * Class FolderManagerTest
 */
class FolderManagerTest extends AbstractBaseTestCase
{
    /**
     * @param MediaFolderInterface     $folder
     * @param int                      $expectedCall
     * @param MediaRepositoryInterface $mediaRepository
     * @param int                      $isDeletable
     *
     * @dataProvider provideFolder
     */
    public function testDeleteFolder(MediaFolderInterface $folder, $expectedCall, MediaRepositoryInterface $mediaRepository, $isDeletable)
    {
        $documentManager = Phake::mock('Doctrine\ODM\MongoDB\DocumentManager');
        $manager = new FolderManager($documentManager, $mediaRepository);
        $manager->deleteFolder($folder);
        Phake::verify($documentManager, Phake::times($expectedCall))->remove($folder);
    }

    /**
     * @param MediaFolderInterface     $folder
     * @param int                      $expectedCall
     * @param MediaRepositoryInterface $mediaRepository
     * @param int                      $isDeletable
     *
     * @dataProvider provideFolder
     */
    public function testIsDeletable(MediaFolderInterface $folder, $expectedCall, MediaRepositoryInterface $mediaRepository, $isDeletable)
    {
        $documentManager = Phake::mock('Doctrine\ODM\MongoDB\DocumentManager');
        $manager = new FolderManager($documentManager, $mediaRepository);
        $this->assertEquals($isDeletable, $manager->isDeletable($folder));
    }

    /**
     * @return array
     */
    public function provideFolder()
    {
        $mediaRepository = Phake::mock('OpenOrchestra\Media\Repository\MediaRepositoryInterface');

        $subfolder0 = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        Phake::when($subfolder0)->getMedias()->thenReturn(new ArrayCollection());
        Phake::when($subfolder0)->getSubFolders()->thenReturn(new ArrayCollection());
        Phake::when($subfolder0)->getId()->thenReturn('subfolder0');
        Phake::when($mediaRepository)->countByFolderId('subfolder0')->thenReturn(0);

        $subfolder1 = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        Phake::when($subfolder1)->getMedias()->thenReturn(new ArrayCollection());
        Phake::when($subfolder1)->getSubFolders()->thenReturn(new ArrayCollection());
        Phake::when($subfolder1)->getId()->thenReturn('subfolder1');
        Phake::when($mediaRepository)->countByFolderId('subfolder1')->thenReturn(0);

        $subfolders = new ArrayCollection();
        $subfolders->add($subfolder0);
        $subfolders->add($subfolder1);

        $folder0 = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        Phake::when($folder0)->getSubFolders()->thenReturn($subfolders);
        Phake::when($folder0)->getId()->thenReturn('folder0');
        Phake::when($mediaRepository)->countByFolderId('folder0')->thenReturn(2);

        $folder1 = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        Phake::when($folder1)->getSubFolders()->thenReturn(new ArrayCollection());
        Phake::when($folder1)->getId()->thenReturn('folder1');
        Phake::when($mediaRepository)->countByFolderId('folder1')->thenReturn(0);

        return array(
            array($folder0, 0, $mediaRepository, false),
            array($folder1, 1, $mediaRepository, true),
        );
    }
}
