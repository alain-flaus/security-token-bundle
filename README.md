YokaiSecurityTokenBundle
========================

Installation
------------

### Add the bundle as dependency with Composer

``` bash
$ php composer.phar require yokai/security-token-bundle
```

### Enable the bundle in the kernel

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        // ...
        new Yokai\SecurityTokenBundle\YokaiSecurityTokenBundle(),
    ];
}
```


### Configuration

``` yaml
# app/config/config.yml

doctrine:
    # ...
    orm:
        # ...
        resolve_target_entities:
            Symfony\Component\Security\Core\User\UserInterface: Your\User\Entity\Class\Name

yokai_security_token:
    tokens:
        reset_password: ~
```

First thing is to define the User entity that your application has defined.
This way, each time a Token will be created, it will be linked automatically to it's User.

Then you can configure all the tokens your applications aims to create.
Each token can have following options :

- `generator` : a service id that implements [`Yokai\SecurityTokenBundle\Generator\TokenGeneratorInterface`](Generator/TokenGeneratorInterface)
- `duration` : a valid [`DateTime::modify`](php.net/manual/datetime.modify.php) argument

Default values fallback to :

- `generator` : [`yokai_security_token.open_ssl_token_generator`](Generator/OpenSslTokenGenerator)
- `duration` : ``+2 days`


Usage
-----

``` php
<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Yokai\SecurityTokenBundle\Manager\TokenManagerInterface;
use Yokai\SecurityTokenBundle\Repository\TokenRepositoryInterface;

class SecurityController extends Controller
{
    public function askResetPasswordAction(Request $request)
    {
        $user = $this->getUserRepository()->findOneByUsername($request->request->get('username'));
        if (!$user) {
            return /* up to you */;
        }

        $this->getTokenManager()->create('reset_password', $user);

        return /* up to you */;
    }

    public function doResetPasswordAction(Request $request)
    {
        $token = null;
        try {
            $token = $this->getTokenRepository()->get($request->query->get('token'));
        } catch(TokenNotFoundException $e) {
            /* there is no token with the asked value */
        } catch(TokenExpiredException $e) {
            /* a token was found, but expired */
        } catch(TokenUsedException $e) {
            /* a token was found, but already used */
        }

        if (!$token) {
            return /* up to you */;
        }

        $user = $token->getUser();
        $user->setPassword($request->request->get('password'));

        $this->getUserManager()->flush($user);

        $this->getTokenManager()->setUsed($token);

        return /* up to you */;
    }

    /**
     * @return TokenManagerInterface
     */
    private function getTokenManager()
    {
        return $this->get('yokai_security_token.resolved.manager');
    }

    /**
     * @return TokenRepositoryInterface
     */
    private function getTokenRepository()
    {
        return $this->get('yokai_security_token.resolved.repository');
    }

    /**
     * @return EntityRepository
     */
    private function getUserRepository()
    {
        return /* up to you */;
    }

    /**
     * @return EntityManager
     */
    private function getUserManager()
    {
        return /* up to you */;
    }
}
```

**askResetPasswordAction** :

The `Token Manager` service will handle creating a security token for you,
according to what you have configured for the purpose you asked.


**doResetPasswordAction** :

The `Token Repository` service will handle retrieving security token for you,
returning it when succeed, and throwing exceptions if something wrong :

- Token not found
- Token expired
- Token already used

The `Token Manager` service then mark the Token as used, so it cannot be used twice.


MIT License
-----------

License can be found [here](https://github.com/yokai-php/security-token-bundle/blob/master/LICENSE).


Authors
-------

The bundle was originally created by [Yann Eugoné](https://github.com/yann-eugone).

See the list of [contributors](https://github.com/yokai-php/security-token-bundle/contributors).
