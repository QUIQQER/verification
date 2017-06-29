<?php

namespace QUI\Verification;

/**
 * VerificationInterface
 *
 * This interface must be implemented to start a verification process via Verifier::startVerification()
 */
interface VerificationInterface
{
    /**
     * Get a unique identifier that identifies this Verification
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Get the duration of a Verification (minutes)
     *
     * @return int|false - duration in minutes;
     * if this method returns false use the module setting default value
     */
    public static function getValidDuration();

    /**
     * Execute this method on successful verification
     *
     * @param string $identifier - Unique Verification identifier
     * @return mixed
     */
    public static function onSuccess($identifier);

    /**
     * Execute this method on unsuccessful verification
     *
     * @param string $identifier - Unique Verification identifier
     * @return mixed
     */
    public static function onError($identifier);

    /**
     * This message is displayed to the user on successful verification
     *
     * @param string $identifier - Unique Verification identifier
     * @return string
     */
    public static function getSuccessMessage($identifier);

    /**
     * This message is displayed to the user on unsuccessful verification
     *
     * @param string $identifier - Unique Verification identifier
     * @return string
     */
    public static function getErrorMessage($identifier);

    /**
     * Automatically redirect the user to this URL on successful verification
     *
     * @param string $identifier - Unique Verification identifier
     * @return string|false - If this method returns false, no redirection takes place
     */
    public static function getRedirectUrl($identifier);
}
