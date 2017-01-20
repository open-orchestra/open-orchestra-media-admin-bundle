<?php

namespace OpenOrchestra\MediaAdminBundle\GenerateForm\Strategies;

use OpenOrchestra\Backoffice\GenerateForm\Strategies\AbstractBlockStrategy;
use OpenOrchestra\ModelInterface\Model\BlockInterface;
use Symfony\Component\Form\FormBuilderInterface;
use OpenOrchestra\Media\DisplayBlock\Strategies\MediaListByKeywordStrategy as BaseMediaListByKeywordStrategy;

/**
 * Class MediaListByKeywordStrategy
 */
class MediaListByKeywordStrategy extends AbstractBlockStrategy
{
    /**
     * @param BlockInterface $block
     *
     * @return bool
     */
    public function support(BlockInterface $block)
    {
        return BaseMediaListByKeywordStrategy::NAME === $block->getComponent();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('keywords', 'oo_keywords_choice', array(
            'is_condition' => true,
            'label' => 'open_orchestra_media_admin.form.media.list.keyword',
            'required' => false,
            'group_id' => 'data',
            'sub_group_id' => 'content',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'media_list_by_keyword';
    }
}
