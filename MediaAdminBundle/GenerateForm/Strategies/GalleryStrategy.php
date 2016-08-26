<?php

namespace OpenOrchestra\MediaAdminBundle\GenerateForm\Strategies;

use OpenOrchestra\Backoffice\GenerateForm\Strategies\AbstractBlockStrategy;
use OpenOrchestra\ModelInterface\Model\BlockInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\Media\DisplayBlock\Strategies\GalleryStrategy as BaseGalleryStrategy;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class GalleryStrategy
 */
class GalleryStrategy extends AbstractBlockStrategy
{
    protected $translator;
    protected $thumbnailConfiguration = array();

    /**
     * @param array               $basicBlockConfiguration
     * @param TranslatorInterface $translator
     * @param array               $thumbnailConfiguration
     */
    public function __construct(array $basicBlockConfiguration, TranslatorInterface $translator, array $thumbnailConfiguration)
    {
        parent::__construct($basicBlockConfiguration);
        $this->translator = $translator;
        $this->thumbnailConfiguration = $thumbnailConfiguration;
    }

    /**
     * @param BlockInterface $block
     *
     * @return bool
     */
    public function support(BlockInterface $block)
    {
        return BaseGalleryStrategy::NAME === $block->getComponent();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $formats = $this->getFormats();
        $allowAdd = true;
        if (isset($options['disabled']) && $options['disabled']) {
            $allowAdd = false;
        }

        $builder
            ->add('id', 'text', array(
                'label' => 'open_orchestra_backoffice.form.block.id',
                'constraints' => new NotBlank(),
            ))
            ->add('thumbnailFormat', 'choice', array(
                'choices' => $formats,
                'label' => 'open_orchestra_media_admin.block.gallery.form.thumbnail_format',
                'constraints' => new NotBlank(),
            ))
            ->add('imageFormat', 'choice', array(
                'choices' => $formats,
                'constraints' => new NotBlank(),
                'label' => 'open_orchestra_media_admin.block.gallery.form.image_format.label',
                'attr' => array('help_text' => 'open_orchestra_media_admin.block.gallery.form.image_format.helper'),
            ))
            ->add('pictures', 'collection', array(
                'type' => 'oo_media_choice',
                'constraints' => new NotBlank(),
                'allow_add' => $allowAdd,
                'attr' => array(
                    'data-prototype-label-add' => $this->translator->trans('open_orchestra_media_admin.block.gallery.form.media.add'),
                    'data-prototype-label-new' => $this->translator->trans('open_orchestra_media_admin.block.gallery.form.media.new'),
                    'data-prototype-label-remove' => $this->translator->trans('open_orchestra_media_admin.block.gallery.form.media.delete'),
                ),
                'label' => 'open_orchestra_media_admin.block.gallery.form.pictures',
            ))
            ->add('width', 'text', array(
                'required' => false,
                'label' => 'open_orchestra_media_admin.block.gallery.form.width',
            ))
            ;
    }

    /**
     * @return array
     */
    protected function getFormats()
    {
        $formats = array();
        $formats[MediaInterface::MEDIA_ORIGINAL] = $this->translator->trans('open_orchestra_media_admin.form.media.original_image');
        foreach ($this->thumbnailConfiguration as $key => $thumbnail) {
            $formats[$key] = $this->translator->trans('open_orchestra_media_admin.form.media.' . $key);
        }

        return $formats;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'gallery';
    }

}
