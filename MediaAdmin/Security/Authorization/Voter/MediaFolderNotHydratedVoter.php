<?php

namespace OpenOrchestra\MediaAdmin\Security\Authorization\Voter;

/**
 * Class MediaFolderNotHydratedVoter
 *
 * Voter checking rights on folder not hydrated
 */
class MediaFolderNotHydratedVoter extends AbstractMediaFolderVoter
{
    /**
     * @param mixed $subject
     *
     * @return bool
     */
    protected function supportSubject($subject)
    {
        return (is_array($subject) &&
            array_key_exists('folderId', $subject) &&
            array_key_exists('path', $subject)
        );
    }

    /**
     * @param array $folder
     *
     * @return string
     */
    protected function getPath($folder)
    {
        return $folder['path'];
    }
}
