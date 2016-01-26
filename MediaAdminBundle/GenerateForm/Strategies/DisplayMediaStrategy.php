<?php

namespace OpenOrchestra\MediaAdminBundle\GenerateForm\Strategies;

use OpenOrchestra\Backoffice\GenerateForm\Strategies\AbstractBlockStrategy;
use OpenOrchestra\ModelInterface\Model\BlockInterface;
use Symfony\Component\Form\FormBuilderInterface;
use OpenOrchestra\Media\DisplayBlock\Strategies\DisplayMediaStrategy as BaseMediaStrategy;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class DisplayMediaStrategy
 */
class DisplayMediaStrategy extends AbstractBlockStrategy
{
    /**
     * @param BlockInterface $block
     *
     * @return bool
     */
    public function support(BlockInterface $block)
    {
        return BaseMediaStrategy::DISPLAY_MEDIA == $block->getComponent();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('picture', 'oo_media_choice', array(
                'constraints' => new NotBlank(),
                'label' => 'open_orchestra_media_admin.block.gallery.form.pictures',
            ))
            ->add('nodeToLink', 'oo_node_choice', array(
                'constraints' => new NotBlank(),
                'label' => 'open_orchestra_media_admin.block.display_media.form.node_link',
                'required' => false
            ));
    }

    /**
     * Return the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'display_media';
    }
}
