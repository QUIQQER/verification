<?php

namespace QUI\Verification;

abstract class AbstractVerification implements VerificationInterface
{
    /**
     * Unique Verification identifier
     *
     * @var string|integer
     */
    protected $identifier;

    /**
     * AbstractVerification constructor.
     *
     * @param string|integer $identifier - Unique Verification identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Get a unique identifier that identifies this Verification
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Get the duration of a Verification (minutes)
     *
     * @param string $identifier - Unique Verification identifier
     * @return int|false - duration in minutes;
     * if this method returns false use the module setting default value
     */
    public static function getValidDuration($identifier)
    {
        return false;
    }

    /**
     * Execute this method on successful verification
     *
     * @param string $identifier - Unique Verification identifier
     * @return void
     */
    public static function onSuccess($identifier)
    {

    }

    /**
     * Execute this method on unsuccessful verification
     *
     * @param string $identifier - Unique Verification identifier
     * @return void
     */
    public static function onError($identifier)
    {

    }

    /**
     * This message is displayed to the user on successful verification
     *
     * @param string $identifier - Unique Verification identifier
     * @return string
     */
    public static function getSuccessMessage($identifier)
    {
        return '';
    }

    /**
     * This message is displayed to the user on unsuccessful verification
     *
     * @param string $identifier - Unique Verification identifier
     * @param string $reason - The reason for the error (see \QUI\Verification\Verifier::REASON_)
     * @return string
     */
    public static function getErrorMessage($identifier, $reason)
    {
        return '';
    }

    /**
     * Automatically redirect the user to this URL on successful verification
     *
     * @param string $identifier - Unique Verification identifier
     * @return string|false - If this method returns false, no redirection takes place
     */
    public static function getOnSuccessRedirectUrl($identifier)
    {
        return false;
    }

    /**
     * Automatically redirect the user to this URL on unsuccessful verification
     *
     * @param string $identifier - Unique Verification identifier
     * @return string|false - If this method returns false, no redirection takes place
     */
    public static function getOnErrorRedirectUrl($identifier)
    {
        return false;
    }
}
