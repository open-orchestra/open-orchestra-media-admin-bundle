<?php

namespace OpenOrchestra\MediaAdminBundle\Transformer;

use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\BaseApi\Transformer\AbstractSecurityCheckerAwareTransformer;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class MediaTypeTransformer
 */
class MediaTypeTransformer extends AbstractSecurityCheckerAwareTransformer
{
    protected $translator;

    /**
     * @param string                        $facadeClass
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TranslatorInterface           $translator
     */
    public function __construct(
        $facadeClass,
        AuthorizationCheckerInterface $authorizationChecker,
        TranslatorInterface $translator
    ){
        parent::__construct($facadeClass, $authorizationChecker);
        $this->translator = $translator;
    }

    /**
     * @param MediaInterface $mixed
     * @param array          $params
     *
     * @return FacadeInterface
     */
    public function transform($mixed, array $params = array())
    {
        $facade = $this->newFacade();

        $facade->name = $mixed->getMediaType();
        $facade->translatedName = $this->translator
            ->trans('open_orchestra_media_admin.media_filter.' . $mixed->getMediaType());

        return $facade;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'media_type';
    }
}
