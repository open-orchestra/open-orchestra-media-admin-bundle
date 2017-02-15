<?php
namespace OpenOrchestra\MediaAdmin\MediaForm\Strategy;


use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\MediaAdmin\FileAlternatives\Strategy\ImageStrategy as ImageAlternativeStrategy;
use Symfony\Component\Form\FormInterface;
use OpenOrchestra\MediaAdmin\MediaForm\MediaFormStrategyInterface;
use OpenOrchestra\MediaAdmin\FileAlternatives\FileAlternativesStrategyInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class ImageStrategy
 */
class ImageStrategy implements MediaFormStrategyInterface
{
    protected $imageAlternativeStrategy;
    protected $objectManager;
    protected $tmpDir;

    /**
     * @param FileAlternativesStrategyInterface $imageAlternativeStrategy
     * @param ObjectManager                     $objectManager
     * @param string                            $tmpDir
     */
    public function __construct(
        FileAlternativesStrategyInterface $imageAlternativeStrategy,
        ObjectManager $objectManager,
        $tmpDir
    ){
        $this->imageAlternativeStrategy = $imageAlternativeStrategy;
        $this->objectManager = $objectManager;
        $this->tmpDir = $tmpDir;
    }

    /**
     * @param MediaInterface $media
     *
     * @return bool
     */
    public function support(MediaInterface $media)
    {
        return ImageAlternativeStrategy::MEDIA_TYPE == $media->getMediaType();
    }

    /**
     * Get the form type associated to $media
     *
     * @return string
     */
    public function getFormType()
    {
        return 'oo_media_image';
    }

    /**
     * Run additional process when the form submission is valid
     *
     * @param MediaInterface $media
     * @param FormInterface  $form
     */
    public function runAdditionalProcess(MediaInterface $media, FormInterface $form)
    {
        $this->cropAlternative($form);
        $this->overrideAlternative($form);
    }

    /**
     * Crop a new Alternative if required
     *
     * @param FormInterface $form
     */
    protected function cropAlternative(FormInterface $form)
    {
        $x = $form->get('x')->getData();
        $y = $form->get('y')->getData();
        $h = $form->get('h')->getData();
        $w = $form->get('w')->getData();
        $format = $form->get('format')->getData();

        if (null != $x && null != $y && null != $h && null != $w && null !== $format) {
            $media = $form->getData();

            $this->imageAlternativeStrategy->cropAlternative($media, $x, $y, $h, $w, $format);

            $this->objectManager->persist($media);
            $this->objectManager->flush();
        }
    }

    /**
     * Override an alternative if required
     *
     * @param FormInterface $form
     */
    protected function overrideAlternative(FormInterface $form)
    {
        $file = $form->get('file')->getData();
        $format = $form->get('format')->getData();

        if (null != $file && null != $format) {
            $media = $form->getData();

            $tmpFileName = time() . '-' . $file->getClientOriginalName();
            $file->move($this->tmpDir, $tmpFileName);
            $tmpFilePath = $this->tmpDir . DIRECTORY_SEPARATOR . $tmpFileName;

            $this->imageAlternativeStrategy->overrideAlternative($media, $tmpFilePath, $format);

            $this->objectManager->persist($media);
            $this->objectManager->flush();
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'image_media_form_strategy';
    }
}