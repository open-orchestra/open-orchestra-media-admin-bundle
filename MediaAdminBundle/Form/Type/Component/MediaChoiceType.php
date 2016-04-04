<?php

namespace OpenOrchestra\MediaAdminBundle\Form\Type\Component;

use OpenOrchestra\MediaAdminBundle\Form\DataTransformer\MediaChoiceTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

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

        $mediaOptions = array();
        if (isset($options['required']) && $options['required'] == true) {
             $mediaOptions['constraints'] = new NotBlank();
             $mediaOptions['error_bubbling'] = false;
        }

        $builder
            ->add('id', 'hidden', $mediaOptions)
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
