<?php

namespace OpenOrchestra\MediaAdminBundle\ExtractReference;

use OpenOrchestra\ModelInterface\Model\StatusableInterface;

/**
 * Interface ExtractReferenceInterface
 */
interface ExtractReferenceInterface
{
    /**
     * @param StatusableInterface $statusableElement
     *
     * @return bool
     */
    public function support(StatusableInterface $statusableElement);

    /**
     * @param string $reference
     *
     * @return bool
     */
    public function supportReference($reference);

    /**
     * @param StatusableInterface $statusableElement
     *
     * @return array
     */
    public function extractReference(StatusableInterface $statusableElement);

    /**
     * Get Reference pattern for $statusableElement
     *
     * @param string $statusableElementId
     *
     * return string
     */
    public function getReferencePattern($statusableElementId);

    /**
     * Get invalidate tag of statusable element for reference
     *
     * @param string $reference
     *
     * @return string|null
     */
    public function getInvalidateTagStatusableElement($reference);

    /**
     * @return string
     */
    public function getName();
}
