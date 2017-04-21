<?php

namespace OpenOrchestra\MediaAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

/**
 * Class MediaBaseType
 */
class MediaBaseType extends AbstractType
{
    protected $frontLanguages;
    protected $mediaClass;

    protected $groupRender = array(
        'information' => array(
            'rank'  => 0,
            'label' => 'open_orchestra_media_admin.form.media.group.information',
        ),
        'usage' => array(
            'rank'  => 1,
            'label' => 'open_orchestra_media_admin.form.media.group.usage',
        ),
    );
    protected $subGroupRender = array(
        'properties' => array(
            'rank' => 0,
            'label' => 'open_orchestra_media_admin.form.media.sub_group.properties',
        ),
    );

    /**
     * @param string $mediaClass
     * @param array  $frontLanguages
     */
    public function __construct($mediaClass, array $frontLanguages)
    {
        $this->mediaClass = $mediaClass;
        $this->frontLanguages = array_keys($frontLanguages);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titles', 'oo_multi_languages', array(
                'label'        => 'open_orchestra_media_admin.form.media.title',
                'languages'    => $this->frontLanguages,
                'group_id'     => 'information',
                'sub_group_id' => 'properties',
            ))
            ->add('mediaFolder', 'document', array(
                'label'        => 'open_orchestra_media_admin.form.media.folder',
                'required'     => true,
                'group_id'     => 'information',
                'sub_group_id' => 'properties',
                'class'        => 'OpenOrchestra\MediaModelBundle\Document\MediaFolder',
                'property'     => 'name',
            ))
            ->add('copyright', null, array(
                'label'        => 'open_orchestra_media_admin.form.media.copyright',
                'required'     => false,
                'group_id'     => 'information',
                'sub_group_id' => 'properties',
            ))
            ->add('keywords', 'oo_keywords_choice', array(
                'label'        => 'open_orchestra_media_admin.form.media.keywords',
                'required'     => false,
                'group_id'     => 'information',
                'sub_group_id' => 'properties',
            ))
        ;

        if (array_key_exists('disabled', $options)) {
            $builder->setAttribute('disabled', $options['disabled']);
        }
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['delete_button'] = $options['delete_button'];
        $view->vars['new_button'] = false;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults($this->getDefaultOptionsValues());
    }

    /**
     * Get Default OptionsValues
     *
     * @return array
     */
    protected function getDefaultOptionsValues()
    {
        return array(
            'data_class'       => $this->mediaClass,
            'delete_button'    => false,
            'group_enabled'    => true,
            'group_render'     => $this->groupRender,
            'sub_group_render' => $this->subGroupRender
        );
    }

    /**
     * Add a group render
     *
     * @param string $key
     * @param array  $group
     */
    protected function addGroupRender($key, array $group)
    {
        $this->groupRender[$key] = $group;
    }

    /**
     * Add a subgroup render
     *
     * @param string $key
     * @param array  $subGroup
     */
    protected function addSubGroupRender($key, array $subGroup)
    {
        $this->subGroupRender[$key] = $subGroup;
    }

    /**
     * @return string The name of this type
     */
    public function getName()
    {
        return 'oo_media_base';
    }
}
