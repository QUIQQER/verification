<?php

namespace QUI\Verification;

abstract class AbstractVerification implements VerificationInterface
{
    /**
     * Unique Verification identifier
     *
     * @var string|int
     */
    protected $identifier;

    /**
     * Additional data
     *
     * @var array
     */
    protected $additionalData = array();

    /**
     * VerificationInterface constructor.
     *
     * @param string|int $identifier - Unique identifier
     * @param array $additionalData (optional) - Additional data
     */
    public function __construct($identifier, $additionalData = array())
    {
        $this->identifier     = $identifier;
        $this->additionalData = $additionalData;
    }

    /**
     * Get a unique identifier that identifies this Verification
     *
     * @return string|int
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Get additional data
     *
     * @return array
     */
    public function getAdditionalData()
    {
        return $this->additionalData;
    }

    /**
     * Get the duration of a Verification (minutes)
     *
     * @return int|false - duration in minutes;
     * if this method returns false use the module setting default value
     */
    public function getValidDuration()
    {
        return false;
    }

    /**
     * Execute this method on successful verification
     *
     * @return void
     */
    abstract public function onSuccess();

    /**
     * Execute this method on unsuccessful verification
     *
     * @return void
     */
    abstract public function onError();

    /**
     * This message is displayed to the user on successful verification
     *
     * @return string
     */
    abstract public function getSuccessMessage();

    /**
     * This message is displayed to the user on unsuccessful verification
     *
     * @param string $reason - The reason for the error (see \QUI\Verification\Verifier::REASON_)
     * @return string
     */
    abstract public function getErrorMessage($reason);

    /**
     * Automatically redirect the user to this URL on successful verification
     *
     * @return string|false - If this method returns false, no redirection takes place
     */
    public function getOnSuccessRedirectUrl()
    {
        return false;
    }

    /**
     * Automatically redirect the user to this URL on unsuccessful verification
     *
     * @return string|false - If this method returns false, no redirection takes place
     */
    public function getOnErrorRedirectUrl()
    {
        return false;
    }

    /**
     * Get type (class name)
     *
     * @return string
     */
    public static function getType()
    {
        return get_called_class();
    }
}
