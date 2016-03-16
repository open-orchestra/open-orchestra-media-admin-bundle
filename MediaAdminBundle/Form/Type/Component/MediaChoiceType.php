<?php

namespace OpenOrchestra\MediaAdminBundle\Form\Type\Component;

use OpenOrchestra\MediaAdminBundle\Form\DataTransformer\MediaChoiceTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class MediaChoiceType
 */
class MediaChoiceType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new MediaChoiceTransformer());

        $builder
            ->add('id', 'hidden')
            ->add('format', 'hidden');

        if (array_key_exists('disabled', $options)) {
            $builder->setAttribute('disabled', $options['disabled']);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oo_media_choice';
    }
}
