<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\FileUtils;

use Phake;
use Imagick;

/**
 * Class AbstractFileUtilsManager
 */
abstract class AbstractFileUtilsManager extends \PHPUnit_Framework_TestCase
{
    protected $fixturesPath;

    protected $generatedFiles = array();

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->fixturesPath = __DIR__ . '/../Fixtures/';
    }

    /**
     * Check if a file has been correctly generated and tag it to be deleted on tearDown
     * 
     * @param string $expectedFile
     * @param string $generatedFile
     */
    protected function assertFileCorrectlyGenerated($expectedFile, $generatedFile)
    {
        $this->generatedFiles[] = $generatedFile;

        $this->assertFileExists($generatedFile);

        $expectedImage = new Imagick($expectedFile);
        $generatedImage = new Imagick($generatedFile);
        $this->assertSame($expectedImage->getImageHeight(), $generatedImage->getImageHeight());
        $this->assertSame($expectedImage->getImageWidth(), $generatedImage->getImageWidth());
    }

    /**
     * tearDown
     */
    public function tearDown()
    {
        foreach ($this->generatedFiles as $generatedFile) {
            if (file_exists($generatedFile)) {
                unlink($generatedFile);
            }
        }
    }
}
