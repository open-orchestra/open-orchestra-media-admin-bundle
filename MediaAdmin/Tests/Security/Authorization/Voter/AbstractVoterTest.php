<?php

namespace OpenOrchestra\MediaAdmin\Tests\Security\Authorization\Voter;

use Phake;
use OpenOrchestra\Backoffice\Tests\Security\Authorization\Voter\AbstractVoterTest as BaseAbstractVoterTest;

/**
 * Class AbstractVoterTest
 */
abstract class AbstractVoterTest extends BaseAbstractVoterTest
{
    /**
     * Create a Phake media folder
     *
     * @return Phake_IMock
     */
    protected function createPhakeMediaFolder()
    {
        return Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
    }

    /**
     * Create a Phake media
     *
     * @return Phake_IMock
     */
    protected function createPhakeMedia()
    {
        $folder = $this->createPhakeMediaFolder();

        $media = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($media)->getMediaFolder()->thenReturn($folder);

        return $media;
    }
}
