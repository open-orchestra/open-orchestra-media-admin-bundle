<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives;

/**
 * Class UploadedMediaValidatorMessage
 */
class UploadedMediaValidatorMessage
{
    protected $message;

    protected $isValid;

    /**
     * @param boolean $isValid
     * @param string  $message
     */
    public function __construct($isValid, $message = '')
    {
        $this->isValid = $isValid;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return $this->isValid;
    }
}
