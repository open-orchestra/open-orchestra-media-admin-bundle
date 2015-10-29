<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Functional\Controller;

use OpenOrchestra\ApiBundle\Tests\Functional\Controller\ApiControllersSecurityTest as BaseApiControllersSecurityTest;

/**
 * Class ApiControllersSecurityTest
 */
class ApiControllersSecurityTest extends BaseApiControllersSecurityTest
{
    /**
     * @return array
     */
    public function provideApiUrl()
    {
        return array(
            array('/api/folder/folderId/delete', 'DELETE'),
        );
    }
}
