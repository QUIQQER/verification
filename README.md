![QUIQQER Verification](bin/images/Readme.jpg)

QUIQQER Verification
========

Verify any action via a special url with a unique hash (i.e. user registration, newsletter opt-in etc.)

Paketname:

    quiqqer/verification


Features
--------

* Create custom Verification classes that handle your verification process(es)
* By starting a Verification you create a unique URL that you can send to the user
* If the user clicks the URL, your Verification class is called
* Each Verification can show a custom message on succes or on error
  * Alternatively you can create redirects to sites of your choosing on success or on error
* Verifications are identified by a unique ID and a unique hash 

Installation
------------

Package name: quiqqer/verification


Collaboration
----------

- Issue Tracker: https://dev.quiqqer.com/quiqqer/verification/issues
- Source Code: https://dev.quiqqer.com/quiqqer/verification


Support
-------

If you found any flaws, have any wishes or suggestions you can send an email to support@pcsg.de to inform us about your concerns. 
We will try to respond to your request and forward it to the responsible developer.


License
-------
PCSG QL-1.0, CC BY-NC-SA 4.0

Usage
--------

### Preparing your project for verification processes
1. Create a site in your project with site type `QUIQQER - Verifier`. This is the site that the user will be directed to when he verifies a process.
2. Create a Verification class that implements `QUI\Verification\VerificationInterface`

### Start a verification process
1. Start the process by calling `QUI\Verification\Verifier::startVerification($Verification)`
2. The method will return a URL that has to be sent to the user (i.e. via Mail)
3. If the user calls the URL the `id` and `hash` parameters are verified against the data from `$Verification`.
4. After that a few things happen:
    * If the verification is successful `$Verification::onSuccess()` will be called
    * If the verification is not successful `$Verification::onError()` will be called
    * If you specified a redirect URL as a return value in `$Verification::getOnSuccessRedirectUrl()` or `$Verification::getOnErrorRedirectUrl()` the user will be immediately redirected
    * If you did not specify a redirect URL the messages returned from `$Verification::getSuccessMessage()` or `$Verification::getErrorMessage()` is shown on the Verifier site

### Misc

* After a valid verification URL has been called, the verification is deleted from the database. This means it can only be called once and produces a general error message if called again.
* The default validity duration of a verification link is **24 hours**. This can be changed by returning an `int` in `$Verficiation::getValidDuration()` that represents the `minutes` a link is valid or by changing the module settings.