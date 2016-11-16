<?php

namespace OpenOrchestra\MediaAdmin\Security;

/**
 * Interface ContributionRoleInterface
 *
 * This interface is never implemented
 * It defines roles available on the media feature
 */
interface ContributionRoleInterface
{
    const MEDIA_FOLDER_CONTRIBUTOR     = 'EDITORIAL_MEDIA_FOLDER_CONTRIBUTOR';     // Can create media folder, edit & delete own media folders
    const MEDIA_FOLDER_SUPER_EDITOR    = 'EDITORIAL_MEDIA_FOLDER_SUPER_EDITOR';    // Can edit someone else's media folder
    const MEDIA_FOLDER_SUPER_SUPRESSOR = 'EDITORIAL_MEDIA_FOLDER_SUPER_SUPRESSOR'; // Can remove someone else's media folder

    const MEDIA_CONTRIBUTOR            = 'EDITORIAL_MEDIA_CONTRIBUTOR';            // Can create media, edit & delete own medias
    const MEDIA_SUPER_EDITOR           = 'EDITORIAL_MEDIA_SUPER_EDITOR';           // Can edit someone else's media
    const MEDIA_SUPER_SUPRESSOR        = 'EDITORIAL_MEDIA_SUPER_SUPRESSOR';        // Can remove someone else's media
}
