## Swipe

Swipe Identity is a free two factor authentication service. Two factor authentication is an approach where a second device must approve each login, so that if passwords are breached, unless the device is also stolen, a login cannot occur.

This is a very secure way of protecting those users who use common passwords against themselves.

At this stage, Sentinel supports Swipe Identity using either "swipe" or "sms" methods. You must also provide your developer account email, password, API key and app code.

### Exceptions

- `Cartalyst\Sentinel\Checkpoints\SwipeIdentityException`

Methods             | Parameters                               | Description
------------------- | ---------------------------------------- | -----------
setUser             | Cartalyst\Sentinel\Users\UserInterface   | Sets a user object on the exception.
getUser             | ..                                       | Retrieves the user object that caused the exception.
setResponse         | SpiExpressSecondFactor                   | Sets the swipe exception response.
getResponse         | ..                                       | Retrieves the swipe exception response.
