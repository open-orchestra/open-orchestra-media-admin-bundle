<?php

namespace OpenOrchestra\MediaAdminBundle\GenerateForm\Strategies;

use OpenOrchestra\Backoffice\GenerateForm\Strategies\AbstractBlockStrategy;
use OpenOrchestra\Media\DisplayBlock\Strategies\SlideshowStrategy as BaseSlideshowStrategy;
use OpenOrchestra\ModelInterface\Model\BlockInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class SlideshowStrategy
 */
class SlideshowStrategy extends AbstractBlockStrategy
{
    protected $translator;

    /**
     * @param array               $basicBlockConfiguration
     * @param TranslatorInterface $translator
     */
    public function __construct(array $basicBlockConfiguration, TranslatorInterface $translator)
    {
        parent::__construct($basicBlockConfiguration);
        $this->translator = $translator;
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
        $builder->add('pictures', 'collection', array(
            'type' => 'oo_media_choice',
            'constraints' => new NotBlank(),
            'allow_add' => $allowAdd,
            'attr' => array(
                'data-prototype-label-add' => $this->translator->trans('open_orchestra_media_admin.block.slideshow.form.media.add'),
                'data-prototype-label-new' => $this->translator->trans('open_orchestra_media_admin.block.slideshow.form.media.new'),
                'data-prototype-label-remove' => $this->translator->trans('open_orchestra_media_admin.block.slideshow.form.media.delete'),
            ),
            'group_id' => 'data',
            'sub_group_id' => 'content',
        ));
        $builder->add('width', 'text', array(
            'constraints' => new NotBlank(),
            'group_id' => 'data',
            'sub_group_id' => 'content',
        ));
        $builder->add('height', 'text', array(
            'constraints' => new NotBlank(),
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
