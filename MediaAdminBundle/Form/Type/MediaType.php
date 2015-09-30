<?php

namespace OpenOrchestra\MediaAdminBundle\Form\Type;

use OpenOrchestra\BackofficeBundle\EventSubscriber\AddSubmitButtonSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

/**
 * Class MediaType
 */
class MediaType extends AbstractType
{
    protected $mediaClass;
    protected $mimeTypes;

    /**
     * @param string $mediaClass
     * @param array  $allowedMimeTypes
     */
    public function __construct($mediaClass, $allowedMimeTypes)
    {
        $this->mediaClass = $mediaClass;
        $this->mimeTypes = $allowedMimeTypes;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', 'file', array(
            'label' => 'open_orchestra_media_admin.form.media.file',
            'label_attr' => array('accept' => implode(',', $this->mimeTypes)),
            'constraints' => array(new File(array(
                    'mimeTypes' => $this->mimeTypes
            )))
        ));

        $builder->addEventSubscriber(new AddSubmitButtonSubscriber());
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
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'media';
    }
}
