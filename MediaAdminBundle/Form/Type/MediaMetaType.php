<?php

namespace OpenOrchestra\MediaAdminBundle\Form\Type;

use OpenOrchestra\BackofficeBundle\EventListener\TranslateValueInitializerListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MediaMetaType
 */
class MediaMetaType extends AbstractType
{
    protected $translateValueInitializer;
    protected $mediaClass;

    /**
     * @param TranslateValueInitializerListener $translateValueInitializer
     * @param string                            $mediaClass
     */
    public function __construct(TranslateValueInitializerListener $translateValueInitializer, $mediaClass)
    {
        $this->mediaClass = $mediaClass;
        $this->translateValueInitializer = $translateValueInitializer;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this->translateValueInitializer, 'preSetData'));

        $builder->add('titles', 'oo_translated_value_collection', array(
            'label' => 'open_orchestra_media_admin.form.media.meta.title',
            'required' => false,
        ));
        $builder->add('alts', 'oo_translated_value_collection', array(
            'label' => 'open_orchestra_media_admin.form.media.meta.alt',
            'required' => false,
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
