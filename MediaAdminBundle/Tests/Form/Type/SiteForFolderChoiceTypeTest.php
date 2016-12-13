<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Form\Type;

use OpenOrchestra\Backoffice\Security\ContributionRoleInterface;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\MediaAdminBundle\Form\Type\SiteForFolderChoiceType;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class SiteForFolderChoiceTypeTest
 */
class SiteForFolderChoiceTypeTest extends AbstractBaseTestCase
{
    protected $site1;
    protected $siteId1 = 'site_id_1';
    protected $siteName1 = 'site_name_1';
    protected $siteId2 = 'site_id_2';
    protected $siteName2 = 'site_name_2';
    protected $site2;
    protected $siteDeleted;
    protected $siteRepository;
    protected $groupA; // site1, ACCESS_MEDIA_FOLDER ok
    protected $groupB; // site2, ACCESS_MEDIA_FOLDER ko
    protected $groupC; // siteDeleted, , ACCESS_MEDIA_FOLDER ok
    protected $user;
    protected $token;
    protected $tokenStorage;
    protected $embedSiteToSiteIdTransformer;
    protected $form;
    protected $choiceList; // site1
    protected $authorizationChecker; // site1

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->site1 = Phake::mock('OpenOrchestra\ModelInterface\Model\SiteInterface');
        Phake::when($this->site1)->getSiteId()->thenReturn($this->siteId1);
        Phake::when($this->site1)->getName()->thenReturn($this->siteName1);
        Phake::when($this->site1)->isDeleted()->thenReturn(false);

        $this->site2 = Phake::mock('OpenOrchestra\ModelInterface\Model\SiteInterface');
        Phake::when($this->site2)->getSiteId()->thenReturn($this->siteId2);
        Phake::when($this->site2)->getName()->thenReturn($this->siteName2);
        Phake::when($this->site2)->isDeleted()->thenReturn(false);

        $this->siteDeleted = Phake::mock('OpenOrchestra\ModelInterface\Model\SiteInterface');
        Phake::when($this->siteDeleted)->getSiteId()->thenReturn('site_id_deleted');
        Phake::when($this->siteDeleted)->getName()->thenReturn('site_name_deleted');

        $this->siteRepository = Phake::mock('OpenOrchestra\ModelInterface\Repository\SiteRepositoryInterface');
        Phake::when($this->siteRepository)->findByDeleted(false)->thenReturn(array($this->site1, $this->site2));

        $this->groupA = Phake::mock('OpenOrchestra\GroupBundle\Document\Group');
        Phake::when($this->groupA)->getSite()->thenReturn($this->site1);

        $this->groupB = Phake::mock('OpenOrchestra\GroupBundle\Document\Group');
        Phake::when($this->groupB)->getSite()->thenReturn($this->site2);

        $this->groupC = Phake::mock('OpenOrchestra\GroupBundle\Document\Group');
        Phake::when($this->groupC)->getSite()->thenReturn($this->siteDeleted);

        $this->user = Phake::mock('OpenOrchestra\UserBundle\Document\User');
        Phake::when($this->user)->getGroups()->thenReturn(array($this->groupA, $this->groupB, $this->groupC));

        $this->token = Phake::mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        Phake::when($this->token)->getUser()->thenReturn($this->user);

        $this->tokenStorage = Phake::mock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        Phake::when($this->tokenStorage)->getToken()->thenReturn($this->token);

        $this->embedSiteToSiteIdTransformer = Phake::mock('OpenOrchestra\MediaAdminBundle\Form\DataTransformer\EmbedSiteToSiteIdTransformer');
        $this->authorizationChecker = Phake::mock(AuthorizationCheckerInterface::class);

        $this->form = new SiteForFolderChoiceType(
            $this->siteRepository,
            $this->tokenStorage,
            $this->embedSiteToSiteIdTransformer,
            $this->authorizationChecker
        );

        $this->choiceList = array($this->siteId1 => $this->siteName1);
    }

    /**
     * Test Name
     */
    public function testName()
    {
        $this->assertSame('oo_site_for_folder_choice', $this->form->getName());
    }

    /**
     * Test Parent
     */
    public function testParent()
    {
        $this->assertSame('choice', $this->form->getParent());
    }

    /**
     * Test buildForm with 'embed' option
     */
    public function testBuildFormWithEmbedOption()
    {
        $builder = Phake::mock('Symfony\Component\Form\FormBuilder');

        $this->form->buildForm($builder, array('embed' => true));

        Phake::verify($builder)->addModelTransformer($this->embedSiteToSiteIdTransformer);
    }

    /**
     * Test buildForm without 'embed' option
     */
    public function testBuildFormWithoutEmbedOption()
    {
        $builder = Phake::mock('Symfony\Component\Form\FormBuilder');

        $this->form->buildForm($builder, array('embed' => false));

        Phake::verify($builder, Phake::never())->addModelTransformer($this->embedSiteToSiteIdTransformer);
    }

    /**
     * Test the default options
     */
    public function testConfigureOptions()
    {
        $resolverMock = Phake::mock('Symfony\Component\OptionsResolver\OptionsResolver');
        Phake::when($this->token)->getRoles()->thenReturn(array());

        $this->form->configureOptions($resolverMock);

        Phake::verify($resolverMock)->setDefaults(array(
                'embed' => false,
            'choices' =>  array(
                $this->siteId1 => $this->siteName1,
                $this->siteId2 => $this->siteName2
            )
        ));
    }


    /**
     * Test the default options
     */
    public function testConfigureOptionsWithSuperAdminUser()
    {
        Phake::when($this->authorizationChecker)->isGranted(Phake::anyParameters())->thenReturn(true);
        $resolverMock = Phake::mock('Symfony\Component\OptionsResolver\OptionsResolver');

        $this->form->configureOptions($resolverMock);

        Phake::verify($resolverMock)->setDefaults(array(
            'embed' => false,
            'choices' =>  array(
                $this->siteId1 => $this->siteName1,
                $this->siteId2 => $this->siteName2
            )
        ));
    }
}
