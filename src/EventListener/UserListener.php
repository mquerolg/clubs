<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;

class UserListener
{
    private $_router;
    private $_requestStack;

    public function __construct(RequestStack $rs, RouterInterface $r)
    {
        $this->_router = $r;
        $this->_requestStack = $rs;
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIONES PARA VALIDAR SI HAY UNA SESION INICIADA
    |--------------------------------------------------------------------------
    */

    public function onKernelRequest(RequestEvent $event)
    {
        $currentRoute = (string)$event->getRequest()->attributes->get('_route');
        $pathInfo = $event->getRequest()->getPathInfo();

        $session = $this->_requestStack->getSession();
        if (!empty($_POST) && !$session->has('user_info_post')) {
            $user_info_post = [
                'user' => $_POST['user'],
                'pass' => $_POST['pass'],
                'perfil' => $_POST['perfil'],
                'ens' => $_POST['ens'],
            ];

            $session->set('user_info_post', $user_info_post);
        }

        if (!$this->isUserOnCronPage($currentRoute, $pathInfo)) {
            if (!$this->isUserLogged()) {
                if (!$this->isUserOnLoginPage($currentRoute, $pathInfo)) {
                    $response = new RedirectResponse($_ENV['URL_LOGOUT']);
                    $event->setResponse($response);
                }
            } elseif ($this->isUserOnLoginPage($currentRoute, $pathInfo)) {
                $response = new RedirectResponse($this->_router->generate('admin'));
                $event->setResponse($response);
            }
        }
    }

    private function isUserLogged()
    {
        $session = $this->_requestStack->getSession();

        if ($_ENV['IS_USER_LOGIN'] === 'true' && !$session->has('user_info')) {
            if ($_ENV['IS_USER_ADMIN'] === 'true') {
                $user_info = [
                    'user' => 'test',
                    'passwd' => 'pass',
                    'role' => true,
                    'library' => 0,
                ];
            } else {
                $user_info = [
                    'user' => 'test',
                    'passwd' => 'pass',
                    'role' => false,
                    'library' => 1,
                ];
            }

            $session->set('user_info', $user_info);
        }

        return $session->has('user_info');
    }

    private function isUserOnLoginPage($currentRoute, $pathInfo)
    {
        return $currentRoute == 'login' || $pathInfo == '/login';
    }

    private function isUserOnCronPage($currentRoute, $pathInfo)
    {
        return $currentRoute == 'downloadFile' || $currentRoute == 'cron' || $pathInfo == '/api/cron';
    }
}
