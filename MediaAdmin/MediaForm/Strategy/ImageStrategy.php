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
     * @param MediaInterface $media
     *
     * @return string
     */
    public function getFormType(MediaInterface $media)
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
        if (null != $form['x']->getData()
            && null != $form['y']->getData()
            && null != $form['h']->getData()
            && null != $form['w']->getData()
        ){
            $media = $form->getData();

            $this->imageAlternativeStrategy->cropAlternative(
                $media,
                $form['x']->getData(),
                $form['y']->getData(),
                $form['h']->getData(),
                $form['w']->getData(),
                $form['format']->getData()
            );

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
        if (null != $form['file']->getData() && null != $form['format']->getData()) {
            $media = $form->getData();
            $file = $form['file']->getData();

            $tmpFileName = time() . '-' . $file->getClientOriginalName();
            $file->move($this->tmpDir, $tmpFileName);
            $tmpFilePath = $this->tmpDir . DIRECTORY_SEPARATOR . $tmpFileName;

            $this->imageAlternativeStrategy->overrideAlternative($media, $tmpFilePath, $form['format']->getData());

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