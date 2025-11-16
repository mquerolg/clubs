<?php

namespace App\Controller;

use App\Entity\Libraries;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    private $_admin_profile = 6754319;
    private $_user;
    private $_pass;
    private $_profile;
    private $_ens;
    private $_session;

    public function __construct(RequestStack $requestStack)
    {
        $request = $requestStack->getCurrentRequest();

        $this->_session = $request->getSession();
        $this->_user = $this->_session->has('user_info_post') ? $this->_session->get('user_info_post')['user'] : $request->get('user');
        $this->_pass = $this->_session->has('user_info_post') ? $this->_session->get('user_info_post')['pass'] : $request->get('pass');
        $this->_profile = $this->_session->has('user_info_post') ? $this->_session->get('user_info_post')['perfil'] : $request->get('perfil');
        $this->_ens = $this->_session->has('user_info_post') ? $this->_session->get('user_info_post')['ens'] : $request->get('ens');
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(): Response
    {
        if ($this->isLogable()) {
            try {
                $conn = oci_connect($this->_user, $this->_pass, $_ENV['DB_HOST']);

                if (!$conn) {
                    $this->render('page/login.html.twig');
                }

                oci_execute(oci_parse($conn, 'SET ROLE ALL'));

                $stid = oci_parse($conn, "SELECT MAX(USR_NOM) AS NOM FROM VUS_USERS WHERE USR_USERNAME = '" . trim($this->_user) . "'");

                oci_execute($stid);

                while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                    $stmt = $row;
                }

                $name = $stmt['NOM'];
                $role_admin = $this->isRoleAdmin();
                $library = null;

                if (!$role_admin && $this->isLibrarian()) {
                    $stid2 = oci_parse($conn, "SELECT LOC_III_ID FROM BIBL_LOCALITZACIONS WHERE LOC_CODI_ENS = '" . trim($this->_ens) . "'");

                    oci_execute($stid2);

                    while ($row = oci_fetch_array($stid2, OCI_ASSOC + OCI_RETURN_NULLS)) {
                        $result = $row;
                    }

                    $code = $result['LOC_III_ID'];

                    $entityInstance = $this->container->get('doctrine')->getRepository(Libraries::class);
                    $library = $entityInstance->findOneBy(['code' => $code]);
                }

                if ($role_admin || !empty($library)) {
                    $user_info = [
                        'user' => $this->_user,
                        'pass' => $this->_pass,
                        'name' => $name,
                        'role' => $role_admin,
                        'library' => $library,
                    ];

                    $this->_session->set('user_info', $user_info);
                }
            } catch (\Throwable $th) {
                $this->render('page/login.html.twig');
            }
        }

        return $this->_session->has('user_info') ?
                $this->redirect('/')
            :
                $this->render('page/login.html.twig')
        ;
    }

    /**
     * get user_info method
     *
     * @return  bool
     */
    public static function getUserInfo($session): array
    {
        return $session->get('user_info');
    }

    /**
     * Value role user
     *
     * @param Session $session
     *
     * @return  bool
     */
    public static function isAdmin($session): bool
    {
        return $_ENV['IS_USER_ADMIN'] === 'true' ||
            ($session->has('user_info') && isset($session->get('user_info')['role']) && $session->get('user_info')['role']);
    }

    /**
     * get role user true when user is admin
     *
     * @return  bool
     */
    private function isRoleAdmin(): bool
    {
        return $this->_profile == $this->_admin_profile ? true : false;
    }

    /**
     * get true when user is logable
     *
     * @return  bool
     */
    private function isLogable()
    {
        return ((!$this->_session->has('user_info') || !is_array($this->_session->get('user_info'))) && !is_null($this->_user) && !is_null($this->_pass));
    }

    /**
     * get if exist asociate library from user
     *
     * @return  bool
     */
    private function isLibrarian()
    {
        return !is_null($this->_ens) && !empty($this->_ens) && !is_null($this->_profile);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(): Response
    {
        $this->_session->invalidate();

        return $this->redirect($_ENV['URL_LOGOUT']);
    }
}
