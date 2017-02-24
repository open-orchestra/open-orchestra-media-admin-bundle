<?php

namespace OpenOrchestra\MediaAdminBundle\GenerateForm\Strategies;

use OpenOrchestra\Backoffice\GenerateForm\Strategies\AbstractBlockStrategy;
use OpenOrchestra\Media\DisplayBlock\Strategies\SlideshowStrategy as BaseSlideshowStrategy;
use OpenOrchestra\ModelInterface\Model\BlockInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class SlideshowStrategy
 */
class SlideshowStrategy extends AbstractBlockStrategy
{
    /**
     * @param array $basicBlockConfiguration
     */
    public function __construct(array $basicBlockConfiguration)
    {
        parent::__construct($basicBlockConfiguration);
    }

    /**
     * @param BlockInterface $block
     *
     * @return bool
     */
    public function support(BlockInterface $block)
    {
        return BaseSlideshowStrategy::NAME === $block->getComponent();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'text', array(
            'label' => 'open_orchestra_backoffice.form.block.id',
            'constraints' => new NotBlank(),
            'group_id' => 'data',
            'sub_group_id' => 'content',
        ));

        $allowAdd = true;
        if (isset($options['disabled']) && $options['disabled']) {
            $allowAdd = false;
        }
        $builder->add('pictures', 'bootstrap_collection', array(
            'type' => 'oo_media_choice',
            'label' => 'open_orchestra_media_admin.block.slideshow.form.pictures',
            'constraints' => new NotBlank(),
            'allow_add' => $allowAdd,
            'allow_delete' => $allowAdd,
            'add_button_text' => 'open_orchestra_media_admin.block.slideshow.form.media.add',
            'delete_button_text' => 'open_orchestra_media_admin.block.slideshow.form.media.delete',
            'group_id' => 'data',
            'sub_group_id' => 'content',
            'sub_widget_col' => 9,
            'button_col' => 3
        ));
        $builder->add('width', 'text', array(
            'constraints' => new NotBlank(),
            'label' => 'open_orchestra_media_admin.block.slideshow.form.width',
            'group_id' => 'data',
            'sub_group_id' => 'content',
        ));
        $builder->add('height', 'text', array(
            'constraints' => new NotBlank(),
            'label' => 'open_orchestra_media_admin.block.slideshow.form.height',
            'group_id' => 'data',
            'sub_group_id' => 'content',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'slideshow';
    }

}
