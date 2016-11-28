<?php

namespace OpenOrchestra\MediaAdmin\Tests\Security\Authorization\Voter;

use OpenOrchestra\Backoffice\Security\ContributionActionInterface;
use OpenOrchestra\Backoffice\Security\ContributionRoleInterface;
use OpenOrchestra\MediaAdmin\Security\ContributionRoleInterface as MediaRoleInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use OpenOrchestra\MediaAdmin\Security\Authorization\Voter\MediaFolderVoter;

/**
 * Class MediaVoterTest
 */
class MediaFolderVoterTest extends AbstractVoterTest
{
    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();

        $this->voter = new MediaFolderVoter($this->perimeterManager);
    }

    /**
     * @return array
     */
    protected function getNotSupportedSubjects()
    {
        $node = $this->createPhakeNode();
        $content = $this->createPhakeContent();
        $trashItem = $this->createPhakeTrashItem();
        $site = $this->createPhakeSite();
        $redirection = $this->createPhakeRedirection();
        $log = $this->createPhakeLog();
        $user = $this->createPhakeUser();
        $group = $this->createPhakeGroup();
        $keyword = $this->createPhakeKeyword();
        $client = $this->createPhakeApiClient();
        $contentType = $this->createPhakeContentType();
        $profile = $this->createPhakeWorkflowProfile();
        $status = $this->createPhakeStatus();
        $media = $this->createPhakeMedia();

        return array(
            'Bad subject : Node'             => array($node,        ContributionActionInterface::READ, array(ContributionRoleInterface::DEVELOPER), true, VoterInterface::ACCESS_ABSTAIN),
            'Bad subject : Content'          => array($content,     ContributionActionInterface::READ, array(ContributionRoleInterface::DEVELOPER), true, VoterInterface::ACCESS_ABSTAIN),
            'Bad subject : Trash Item'       => array($trashItem,   ContributionActionInterface::READ, array(ContributionRoleInterface::DEVELOPER), true, VoterInterface::ACCESS_ABSTAIN),
            'Bad subject : Site'             => array($site,        ContributionActionInterface::READ, array(ContributionRoleInterface::DEVELOPER), true, VoterInterface::ACCESS_ABSTAIN),
            'Bad subject : Redirection'      => array($redirection, ContributionActionInterface::READ, array(ContributionRoleInterface::DEVELOPER), true, VoterInterface::ACCESS_ABSTAIN),
            'Bad subject : Log'              => array($log,         ContributionActionInterface::READ, array(ContributionRoleInterface::DEVELOPER), true, VoterInterface::ACCESS_ABSTAIN),
            'Bad subject : User'             => array($user,        ContributionActionInterface::READ, array(ContributionRoleInterface::DEVELOPER), true, VoterInterface::ACCESS_ABSTAIN),
            'Bad subject : Group'            => array($group,       ContributionActionInterface::READ, array(ContributionRoleInterface::DEVELOPER), true, VoterInterface::ACCESS_ABSTAIN),
            'Bad subject : Keyword'          => array($keyword,     ContributionActionInterface::READ, array(ContributionRoleInterface::DEVELOPER), true, VoterInterface::ACCESS_ABSTAIN),
            'Bad subject : Api client'       => array($client,      ContributionActionInterface::READ, array(ContributionRoleInterface::DEVELOPER), true, VoterInterface::ACCESS_ABSTAIN),
            'Bad subject : Content type'     => array($contentType, ContributionActionInterface::READ, array(ContributionRoleInterface::DEVELOPER), true, VoterInterface::ACCESS_ABSTAIN),
            'Bad subject : Workflow profile' => array($profile,     ContributionActionInterface::READ, array(ContributionRoleInterface::DEVELOPER), true, VoterInterface::ACCESS_ABSTAIN),
            'Bad subject : Status'           => array($status,      ContributionActionInterface::READ, array(ContributionRoleInterface::DEVELOPER), true, VoterInterface::ACCESS_ABSTAIN),
            'Bad subject : Media'            => array($media,       ContributionActionInterface::READ, array(ContributionRoleInterface::DEVELOPER), true, VoterInterface::ACCESS_ABSTAIN),
        );
    }

    /**
     * @return array
     */
    protected function getNotSupportedAttributes()
    {
        $folder = $this->createPhakeMediaFolder();

        return array(
            'Bad action : Trash Purge'   => array($folder, ContributionActionInterface::TRASH_PURGE,   array(ContributionRoleInterface::DEVELOPER), true, VoterInterface::ACCESS_ABSTAIN),
            'Bad action : Trash Restore' => array($folder, ContributionActionInterface::TRASH_RESTORE, array(ContributionRoleInterface::DEVELOPER), true, VoterInterface::ACCESS_ABSTAIN),
        );
    }

    /**
     * @return array
     */
    protected function getNotInPerimeter()
    {
        $folder = $this->createPhakeMediaFolder();

        return array(
            'Not in perimeter : Add'    => array($folder, ContributionActionInterface::CREATE, array(MediaRoleInterface::MEDIA_FOLDER_CONTRIBUTOR),     false, VoterInterface::ACCESS_DENIED),
            'Not in perimeter : Read'   => array($folder, ContributionActionInterface::READ,   array(),                                                 false, VoterInterface::ACCESS_DENIED),
            'Not in perimeter : Edit'   => array($folder, ContributionActionInterface::EDIT,   array(MediaRoleInterface::MEDIA_FOLDER_SUPER_EDITOR),    false, VoterInterface::ACCESS_DENIED),
            'Not in perimeter : Delete' => array($folder, ContributionActionInterface::DELETE, array(MediaRoleInterface::MEDIA_FOLDER_SUPER_SUPRESSOR), false, VoterInterface::ACCESS_DENIED),
        );
    }

    /**
     * @return array
     */
    protected function getBadRoles()
    {
        $folder = $this->createPhakeMediaFolder();

        return array(
            'Bad role (Edit) : None'                      => array($folder, ContributionActionInterface::EDIT,   array(),                                                   true, VoterInterface::ACCESS_DENIED),
            'Bad role (Edit) : Content contributor'       => array($folder, ContributionActionInterface::EDIT,   array(ContributionRoleInterface::CONTENT_CONTRIBUTOR),     true, VoterInterface::ACCESS_DENIED),
            'Bad role (Edit) : Content super editor'      => array($folder, ContributionActionInterface::EDIT,   array(ContributionRoleInterface::CONTENT_SUPER_EDITOR),    true, VoterInterface::ACCESS_DENIED),
            'Bad role (Edit) : Content super supressor'   => array($folder, ContributionActionInterface::EDIT,   array(ContributionRoleInterface::CONTENT_SUPER_SUPRESSOR), true, VoterInterface::ACCESS_DENIED),
            'Bad role (Edit) : Site Admin'                => array($folder, ContributionActionInterface::EDIT,   array(ContributionRoleInterface::SITE_ADMIN),              true, VoterInterface::ACCESS_DENIED),
            'Bad role (Edit) : Trash Restorer'            => array($folder, ContributionActionInterface::EDIT,   array(ContributionRoleInterface::TRASH_RESTORER),          true, VoterInterface::ACCESS_DENIED),
            'Bad role (Edit) : Trash Supressor'           => array($folder, ContributionActionInterface::EDIT,   array(ContributionRoleInterface::TRASH_SUPRESSOR),         true, VoterInterface::ACCESS_DENIED),
            'Bad role (Edit) : Media contributor'         => array($folder, ContributionActionInterface::EDIT,   array(MediaRoleInterface::MEDIA_CONTRIBUTOR),              true, VoterInterface::ACCESS_DENIED),
            'Bad role (Edit) : Media super editor'        => array($folder, ContributionActionInterface::EDIT,   array(MediaRoleInterface::MEDIA_SUPER_EDITOR),             true, VoterInterface::ACCESS_DENIED),
            'Bad role (Edit) : Media super supressor'     => array($folder, ContributionActionInterface::EDIT,   array(MediaRoleInterface::MEDIA_SUPER_SUPRESSOR),          true, VoterInterface::ACCESS_DENIED),
            'Bad role (Delete) : None'                    => array($folder, ContributionActionInterface::DELETE, array(),                                                   true, VoterInterface::ACCESS_DENIED),
            'Bad role (Delete) : Content contributor'     => array($folder, ContributionActionInterface::DELETE, array(ContributionRoleInterface::CONTENT_CONTRIBUTOR),     true, VoterInterface::ACCESS_DENIED),
            'Bad role (Delete) : Content super editor'    => array($folder, ContributionActionInterface::DELETE, array(ContributionRoleInterface::CONTENT_SUPER_EDITOR),    true, VoterInterface::ACCESS_DENIED),
            'Bad role (Delete) : Content super supressor' => array($folder, ContributionActionInterface::DELETE, array(ContributionRoleInterface::CONTENT_SUPER_SUPRESSOR), true, VoterInterface::ACCESS_DENIED),
            'Bad role (Delete) : Site Admin'              => array($folder, ContributionActionInterface::DELETE, array(ContributionRoleInterface::SITE_ADMIN),              true, VoterInterface::ACCESS_DENIED),
            'Bad role (Delete) : Trash Restorer'          => array($folder, ContributionActionInterface::DELETE, array(ContributionRoleInterface::TRASH_RESTORER),          true, VoterInterface::ACCESS_DENIED),
            'Bad role (Delete) : Trash Supressor'         => array($folder, ContributionActionInterface::DELETE, array(ContributionRoleInterface::TRASH_SUPRESSOR),         true, VoterInterface::ACCESS_DENIED),
            'Bad role (Delete) : Media contributor'       => array($folder, ContributionActionInterface::DELETE, array(MediaRoleInterface::MEDIA_CONTRIBUTOR),              true, VoterInterface::ACCESS_DENIED),
            'Bad role (Delete) : Media super editor'      => array($folder, ContributionActionInterface::DELETE, array(MediaRoleInterface::MEDIA_SUPER_EDITOR),             true, VoterInterface::ACCESS_DENIED),
            'Bad role (Delete) : Media super supressor'   => array($folder, ContributionActionInterface::DELETE, array(MediaRoleInterface::MEDIA_SUPER_SUPRESSOR),          true, VoterInterface::ACCESS_DENIED),
        );
    }

    /**
     * @return array
     */
    protected function getOkVotes()
    {
        $folder = $this->createPhakeMediaFolder();

        return array(
            'Ok : Read'   => array($folder, ContributionActionInterface::READ,   array(),                                                 true, VoterInterface::ACCESS_GRANTED),
            'Ok : Add'    => array($folder, ContributionActionInterface::CREATE, array(MediaRoleInterface::MEDIA_FOLDER_CONTRIBUTOR),     true, VoterInterface::ACCESS_GRANTED),
            'Ok : Edit'   => array($folder, ContributionActionInterface::EDIT,   array(MediaRoleInterface::MEDIA_FOLDER_SUPER_EDITOR),    true, VoterInterface::ACCESS_GRANTED),
            'Ok : Delete' => array($folder, ContributionActionInterface::DELETE, array(MediaRoleInterface::MEDIA_FOLDER_SUPER_SUPRESSOR), true, VoterInterface::ACCESS_GRANTED),
        );
    }
}
