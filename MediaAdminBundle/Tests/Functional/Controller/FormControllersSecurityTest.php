<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Functional\Controller;

use OpenOrchestra\BackofficeBundle\Tests\Functional\Controller\FormControllersSecurityTest as BaseFormControllersSecurityTest;

/**
 * Class FormControllersSecurityTest
 */
class FormControllersSecurityTest extends BaseFormControllersSecurityTest
{
    /**
     * @return array
     */
    public function provideApiUrl()
    {
        return array(
            array('/admin/folder/form/folderId'),
            array('/admin/folder/new/parentId'),
            array('/admin/folder/list'),
        );
    }
}
