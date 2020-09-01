<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends ApiAuthenticator
{
    use TargetPathTrait;

    public function supports(Request $request)
    {
        $route = $request->attributes->get('_route');
        return 'login_form' === $route && $request->isMethod('POST');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $targetPath = $this->getTargetPath($request->getSession(), $providerKey) ?: '/';
        return new RedirectResponse($targetPath);
    }
}
