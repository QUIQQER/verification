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
     * VerificationInterface constructor.
     *
     * @param string|int $identifier - Unique identifier
     * @param array $additionalData (optional) - Additional data
     */
    public function __construct($identifier, $additionalData = array());

    /**
     * Get a unique identifier that identifies this Verification
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Get additional data
     *
     * @return array
     */
    public function getAdditionalData();

    /**
     * Get the duration of a Verification (minutes)
     *
     * @return int|false - duration in minutes;
     * if this method returns false use the module setting default value
     */
    public function getValidDuration();

    /**
     * Execute this method on successful verification
     *
     * @return void
     */
    public function onSuccess();

    /**
     * Execute this method on unsuccessful verification
     *
     * @return void
     */
    public function onError();

    /**
     * This message is displayed to the user on successful verification
     *
     * @return string
     */
    public function getSuccessMessage();

    /**
     * This message is displayed to the user on unsuccessful verification
     *
     * @param string $reason - The reason for the error (see \QUI\Verification\Verifier::REASON_*)
     * @return string
     */
    public function getErrorMessage($reason);

    /**
     * Automatically redirect the user to this URL on successful verification
     *
     * @return string|false - If this method returns false, no redirection takes place
     */
    public function getOnSuccessRedirectUrl();

    /**
     * Automatically redirect the user to this URL on unsuccessful verification
     *
     * @return string|false - If this method returns false, no redirection takes place
     */
    public function getOnErrorRedirectUrl();
}
