<?php

namespace OpenOrchestra\MediaAdminBundle\Form\Type;

use OpenOrchestra\Backoffice\Context\ContextBackOfficeInterface;
use OpenOrchestra\Media\Model\MediaInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use OpenOrchestra\Media\Manager\MediaStorageManagerInterface;

/**
 * Class MediaImageType
 */
class MediaImageType extends MediaBaseType
{
    protected $thumbnailConfig;
    protected $storageManager;

    /**
     * @param ContextBackOfficeInterface   $currentSiteManager
     * @param string                       $mediaClass
     * @param array                        $frontLanguages
     * @param array                        $thumbnailConfig
     * @param MediaStorageManagerInterface $storageManager
     */
    public function __construct(
        ContextBackOfficeInterface $currentSiteManager,
        $mediaClass,
        array $frontLanguages,
        array $thumbnailConfig,
        MediaStorageManagerInterface $storageManager
    ){
        parent::__construct(
            $currentSiteManager,
            $mediaClass,
            $frontLanguages
        );
        $this->thumbnailConfig = $thumbnailConfig;
        $this->storageManager = $storageManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('format', 'choice', array(
                'choices'      => $this->getChoices(),
                'label'        => 'open_orchestra_media_admin.form.media.format',
                'empty_value'  => 'open_orchestra_media_admin.form.media.original_image',
                'required'     => false,
                'mapped'       => false,
                'group_id'     => 'information',
                'sub_group_id' => 'format',
            ))
            ->add('coordinates', 'form', array(
                'mapped'       => false,
                'label'        => false,
                'group_id'     => 'information',
                'sub_group_id' => 'format',
            ))
            ->add('files', 'form', array(
                'mapped'       => false,
                'label'        => false,
                'group_id'     => 'information',
                'sub_group_id' => 'format',
            ));
        $coordinates =  $builder->get('coordinates');
        $files =  $builder->get('files');
        foreach ($this->thumbnailConfig as $format => $parameters) {
            $coordinates->add($format, 'form', array(
                'label' => 'open_orchestra_media_admin.form.media.' . $format,
            ));
            $files->add($format, 'form', array(
                'label' => 'open_orchestra_media_admin.form.media.' . $format,
            ));
            $coordinates->get($format)
                ->add('x', 'integer', array(
                    'label'    => 'open_orchestra_media_admin.form.media.x',
                    'required' => false,
                ))
                ->add('y', 'integer', array(
                    'label'    => 'open_orchestra_media_admin.form.media.y',
                    'required' => false,
                ))
                ->add('w', 'integer', array(
                    'label'    => 'open_orchestra_media_admin.form.media.w',
                    'required' => false,
                ))
                ->add('h', 'integer', array(
                    'label'        => 'open_orchestra_media_admin.form.media.h',
                    'required' => false,
                ));
            $files->get($format)
                ->add('file', 'file', array(
                    'label' => false,
                    'mapped'   => false,
                ));
        }
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $media = $form->getData();
        if (!empty($media->getAlternatives())) {
            $view->vars['alternatives'] = array(
                MediaInterface::MEDIA_ORIGINAL => $this->storageManager->getUrl($media->getFilesystemName())
            );
            foreach ($this->thumbnailConfig as $key => $params) {
                $url = $this->storageManager->getUrl($media->getAlternative($key));
                if (null !== $url) {
                    $view->vars['alternatives'][$key] = $url;
                }
            }
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $this->addSubGroupRender('format', array(
            'rank' => 1,
            'label' => 'open_orchestra_media_admin.form.media.sub_group.format',
        ));
        parent::configureOptions($resolver);
    }

    /**
     * @return array
     */
    protected function getChoices()
    {
        $choices = array();

        foreach ($this->thumbnailConfig as $key => $thumbnail) {
            $choices[$key] = 'open_orchestra_media_admin.form.media.' . $key;
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oo_media_base';
    }

    /**
     * @return string The name of this type
     */
    public function getName()
    {
        return 'oo_media_image';
    }
}
