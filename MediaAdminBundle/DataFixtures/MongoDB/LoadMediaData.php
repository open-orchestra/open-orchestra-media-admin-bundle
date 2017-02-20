<?php

namespace OpenOrchestra\MediaAdminBundle\DataFixtures\MongoDB;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use OpenOrchestra\ModelInterface\DataFixtures\OrchestraFunctionalFixturesInterface;

/**
 * Class LoadMediaData
 */
class LoadMediaData
    extends AbstractFixture
    implements ContainerAwareInterface, OrderedFixtureInterface, OrchestraFunctionalFixturesInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $logoOrchestra = $this->generateMedia(
            'logo-orchestra.png',
            'mediaFolder-transparentImages',
            'logo Open-Orchestra',
            array('keyword-lorem'),
            array(
                'en' => array('title' => 'Open Orchestra Logo'),
                'fr' => array('title' => 'Logo Open Orchestra'),
                'de' => array('title' => 'Open Orchestra Logo')
            )
        );
        $this->addReference('logo-orchestra', $logoOrchestra);

        $pdf = $this->generateMedia(
            'sample-pdf.pdf',
            'mediaFolder-pdfFolder',
            'Demo pdf',
            array(),
            array(
                'en' => array('title' => 'demo pdf'),
                'fr' => array('title' => 'pdf de démo'),
                'de' => array('title' => 'pdf-Demo')
            )
        );
        $this->addReference('sample-pdf', $pdf);

        for ($i = 1; $i < 5; $i++) {
            $this->generateMedia(
                '0' . $i . '.jpg',
                'mediaFolder-rootImages',
                'Image 0' . $i,
                array('keyword-lorem', 'keyword-dolor'),
                array(
                    'en' => array('title' => 'image 0' . $i),
                    'fr' => array('title' => 'image 0' . $i),
                    'de' => array('title' => 'image 0' . $i)
                )
            );
        }

        // Launch manually the method as there is no KernelEvents::TERMINATE fired in fixture mode
        $this->container->get('open_orchestra_media_admin.subscriber.create_media')->generateAlternatives();
    }

    /**
     * Generate a media
     *
     * @param string $fileName
     * @param string $folderReference
     * @param string $name
     * @param array  $keywordReferencesArray
     * @param array  $languagesArray
     */
    protected function generateMedia(
        $fileName,
        $folderReference,
        $name,
        array $keywordReferencesArray,
        array $languagesArray
    ) {
        $folderId = $this->getReference($folderReference)->getId();
        $filePath = __DIR__ . '/Files/' . $fileName;
        $tmpFilePath = $this->container->getParameter('open_orchestra_media_admin.tmp_dir')
            . DIRECTORY_SEPARATOR . $fileName;

        copy($filePath, $tmpFilePath);

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $tmpFilePath);
        finfo_close($finfo);

        $uploadedFile = new UploadedFile($tmpFilePath, $fileName, $mimeType);

        $saveMediaManager = $this->container->get('open_orchestra_media_admin.manager.save_media');
        $media = $saveMediaManager->initializeMediaFromUploadedFile($uploadedFile, $folderId);

        $media->setName($name);
        foreach ($keywordReferencesArray as $keywordReference) {
            $media->addKeyword($this->getReference($keywordReference));
        }
        foreach ($languagesArray as $language => $labels) {
            $media->addTitle($language, $labels['title']);
        }

        $saveMediaManager->saveMedia($media);

        return $media;
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 351;
    }
}
