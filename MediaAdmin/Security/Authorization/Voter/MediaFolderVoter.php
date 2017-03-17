<?php

namespace OpenOrchestra\MediaAdmin\Security\Authorization\Voter;

/**
 * Class MediaFolderVoter
 *
 * Voter checking rights on media folder management
 */
class MediaFolderVoter extends AbstractMediaFolderVoter
{
    /**
     * @param mixed $subject
     *
     * @return bool
     */
    protected function supportSubject($subject)
    {
        return $this->supportClasses(
            $subject,
            array('OpenOrchestra\Media\Model\MediaFolderInterface')
        );
    }

    /**
     * @param array $folder
     *
     * @return string
     */
    protected function getPath($folder)
    {
        return $folder->getPath();
    }
}
