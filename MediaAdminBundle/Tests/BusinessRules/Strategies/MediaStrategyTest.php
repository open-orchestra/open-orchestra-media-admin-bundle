<?php
namespace OpenOrchestra\BackOffice\Tests\BusinessRules\Strategies;

use OpenOrchestra\Backoffice\Security\ContributionActionInterface;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use OpenOrchestra\Media\Model\MediaInterface;
use Phake;
use OpenOrchestra\MediaAdminBundle\BusinessRules\Strategies\MediaStrategy;

/**
 * Class MediaStrategyTest
 */
class MediaStrategyTest extends AbstractBaseTestCase
{
    protected $strategy;

    /**
     * setUp
     */
    public function setUp()
    {
        $this->strategy = new MediaStrategy();
    }

    /**
     * @param boolean $isUsed
     * @param boolean $isGranted
     *
     * @dataProvider provideDeletable
     */
    public function testCanDelete($isUsed, $isGranted)
    {
        $media = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($media)->isUsed()->thenReturn($isUsed);
        $this->assertSame($isGranted, $this->strategy->canDelete($media, array()));
    }

    /**
     * provide media isDeletable
     *
     * @return array
     */
    public function provideDeletable()
    {
        return array(
            array(true, false),
            array(false, true),
        );
    }

    /**
     * test getActions
     */
    public function testGetActions()
    {
        $this->assertEquals(array(
            ContributionActionInterface::DELETE => 'canDelete',
        ), $this->strategy->getActions());
    }

    /**
     * test getActions
     */
    public function testType()
    {
        $this->assertEquals(MediaInterface::ENTITY_TYPE, $this->strategy->getType());
    }
}
