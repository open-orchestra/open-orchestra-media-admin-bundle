<?php

namespace OpenOrchestra\MediaAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FolderType
 */
class FolderType extends AbstractType
{
    protected $folderClass;
    protected $backLanguages;

    /**
     * @param string $folderClass
     * @param array  $backLanguages
     */
    public function __construct($folderClass, array $backLanguages)
    {
        $this->folderClass = $folderClass;
        $this->backLanguages = $backLanguages;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('names', 'oo_multi_languages', array(
                'label'        => 'open_orchestra_media_admin.form.folder.name',
                'languages'    => $this->backLanguages,
                'sub_group_id' => 'property',
            ));

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
        $view->vars['enable_delete_button'] = $options['enable_delete_button'];
        $view->vars['delete_help_text'] = $options['delete_help_text'];
        $view->vars['new_button'] = $options['new_button'];
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->folderClass,
            'group_enabled' => true,
            'delete_button' => false,
            'enable_delete_button' => false,
            'delete_help_text' => 'open_orchestra_backoffice.form.folder.delete_help_text',
            'new_button' => false,
            'sub_group_render' => array(
                'property' => array(
                    'rank' => 0,
                    'label' => 'open_orchestra_media_admin.form.folder.sub_group.property',
                )
            )
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'oo_folder';
    }

}
