<?php

namespace OpenOrchestra\MediaAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MediaMetaType
 */
class MediaMetaType extends AbstractType
{
    protected $frontLanguages;
    protected $mediaClass;

    /**
     * @param string $mediaClass
     * @param array  $frontLanguages
     */
    public function __construct($mediaClass, array $frontLanguages)
    {
        $this->mediaClass = $mediaClass;
        $this->frontLanguages = array_keys($frontLanguages);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('titles', 'oo_multi_languages', array(
            'label' => 'open_orchestra_media_admin.form.media.meta.title',
            'required' => false,
            'languages' => $this->frontLanguages
        ));
        $builder->add('alts', 'oo_multi_languages', array(
            'label' => 'open_orchestra_media_admin.form.media.meta.alt',
            'required' => false,
            'languages' => $this->frontLanguages
        ));
        $builder->add('copyright', null, array(
            'label' => 'open_orchestra_media_admin.form.media.meta.copyright',
            'required' => false,
        ));
        $builder->add('comment', 'textarea', array(
            'label' => 'open_orchestra_media_admin.form.media.meta.comment',
            'required' => false,
        ));
        $builder->add('keywords', 'oo_keywords_choice', array(
            'label' => 'open_orchestra_media_admin.form.media.meta.keywords',
            'required' => false
        ));

        if (array_key_exists('disabled', $options)) {
            $builder->setAttribute('disabled', $options['disabled']);
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->mediaClass,
        ));
    }

    /**
     * @return string The name of this type
     */
    public function getName()
    {
        return 'oo_media_meta';
    }
}
