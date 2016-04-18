<?php

namespace OpenOrchestra\MediaAdminBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class MediaCropType
 */
class MediaCropType extends MediaSelectFormatType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('x', 'hidden');
        $builder->add('y', 'hidden');
        $builder->add('h', 'hidden');
        $builder->add('w', 'hidden');
        parent::buildForm($builder, $options);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'oo_media_crop';
    }
}
