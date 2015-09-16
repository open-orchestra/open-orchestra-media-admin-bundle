<?php

namespace OpenOrchestra\MediaAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class MediaCropType
 */
class MediaCropType extends AbstractType
{
    protected $thumbnailConfig;
    protected $translator;

    /**
     * @param array               $thumbnailConfig
     * @param TranslatorInterface $translator
     */
    public function __construct(array $thumbnailConfig, TranslatorInterface $translator)
    {
        $this->thumbnailConfig = $thumbnailConfig;
        $this->translator = $translator;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');
        $builder->add('x', 'hidden');
        $builder->add('y', 'hidden');
        $builder->add('h', 'hidden');
        $builder->add('w', 'hidden');
        $builder->add('format', 'choice', array(
            'choices' => $this->getChoices(),
            'label' => 'open_orchestra_media_admin.form.media.format',
            'empty_value' => 'open_orchestra_media_admin.form.media.original_image',
            'required' => false,
        ));
    }

    /**
     * @return array
     */
    protected function getChoices()
    {
        $choices = array();

        foreach ($this->thumbnailConfig as $key => $thumbnail) {
            $choices[$key] = $this->translator->trans('open_orchestra_media_admin.form.media.' . $key);
        }

        return $choices;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'media_crop';
    }
}
