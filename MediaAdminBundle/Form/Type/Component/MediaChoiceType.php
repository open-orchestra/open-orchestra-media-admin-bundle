<?php

namespace OpenOrchestra\MediaAdminBundle\Form\Type\Component;

use OpenOrchestra\MediaAdminBundle\Form\DataTransformer\MediaChoiceTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        if (isset($options['required']) && true === $options['required']) {
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
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'filter' => ''
        ));
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['filter'] = $options['filter'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oo_media_choice';
    }
}
