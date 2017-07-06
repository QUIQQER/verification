verification
========

Verify any action via a special url with a unique hash (i.e. user registration, newsletter opt-in etc.)

Paketname:

    quiqqer/verification


Features
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

Installation
------------

Package name: quiqqer/verification


Collaboration
----------

- Issue Tracker: https://dev.quiqqer.com/quiqqer/verification/issues
- Source Code: https://dev.quiqqer.com/quiqqer/verification


Support
-------

Falls Sie einen Fehler gefunden haben oder Verbesserungen wünschen,
senden Sie bitte eine E-Mail an support@pcsg.de.


License
-------


Developers
--------

- Patrick Müller <__p.mueller@pcsg.de__>