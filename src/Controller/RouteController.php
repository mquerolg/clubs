<?php

namespace App\Controller;

use App\Controller\Admin\LotsCrudController;
use App\Diba\DibaApi;
use App\Diba\Helpers\ConfigManualHelper as Manual;
use App\Diba\Helpers\CronHelper;
use App\Diba\Helpers\OptionsHelper as Options;
use App\Entity\Clubs;
use App\Entity\Libraries;
use App\Entity\Lots;
use App\Entity\Shipments;
use App\Entity\ShipmentsMiddle;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RouteController extends DashboardController
{
    /**
     * @Route("/", name="admin")
     */
    public function index(): Response
    {
        $routeBuilder = $this->container->get(AdminUrlGenerator::class);
        $url = $routeBuilder->setController(LotsCrudController::class)->generateUrl();
        return $this->redirect($url);
    }

    /**
     * @Route("/api/libraries", name="libraries")
     */
    public function libraries(): Response
    {
        $libraries = $this->container->get('doctrine')->getRepository(Libraries::class)->findBy(['active' => 1], ['municipality' => 'ASC']);
        $response = new Response(json_encode($libraries, JSON_UNESCAPED_UNICODE));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/api/routes/{id}/library", name="routeById")
     */
    public function route($id): Response
    {
        $libraries = $this->container->get('doctrine')->getRepository(Libraries::class)->findBy(['active' => 1, 'id' => $id]);

        $sended = [
            'route' => '',
            'lot' => null,
            'date' => '',
            'intDate' => null,
        ];

        if (!empty($libraries) && isset($libraries[0])) {
            $route_id = $libraries[0]->getLocalization()->getRoute();
            $middle = $this->container->get('doctrine')->getRepository(ShipmentsMiddle::class)->findBy(['id' => $route_id]);

            if (!empty($middle) && isset($middle[0])) {
                $route = $middle[0]->getTramDesc();
                $interval = Options::get('max_entry_date') ?? 0;
                $date = new \DateTime('now');
                $date = $date->format('d/m/Y');
                $date = \DateTime::createFromFormat('d/m/Y', $date);
                $routes = $this->container->get('doctrine')->getRepository(Shipments::class)->findBy(['route' => $route]);

                foreach ($routes as $route) {
                    $route_date = \DateTime::createFromFormat('d/m/Y', $route->getStartDate());
                    if ($route_date) {
                        $route_date->sub(new \DateInterval('P' . $interval . 'D'));

                        if ($route_date >= $date && (is_null($sended['intDate']) || $sended['intDate'] > $route_date)) {
                            $sended = [
                                'route' => $route->getRoute(),
                                'lot' => $route->getLot(),
                                'date' => $route->getStartDate(),
                                'intDate' => $route_date,
                            ];
                        }
                    }
                }
            }
        }

        $response = new Response(json_encode($sended));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/api/clubs", name="clubs")
     */
    public function clubs(): Response
    {
        $clubs = $this->container->get('doctrine')->getRepository(Clubs::class)->findBy(['active' => 1]);
        $response = new Response(json_encode($clubs, JSON_UNESCAPED_UNICODE));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/api/clubs/{id}/library", name="clubsByLibrary")
     */
    public function clubsByLibrary(int $id): Response
    {
        $club = $this->container->get('doctrine')->getRepository(Clubs::class)->findBy(['libraryId' => $id, 'active' => 1]);
        $response = new Response(json_encode($club, JSON_UNESCAPED_UNICODE));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/api/lots/{id}/lot", name="lotsByLot")
     */
    public function lotsByLot(int $id): Response
    {
        $lot = $this->container->get('doctrine')->getRepository(Lots::class)->findBy(['id' => $id, 'active' => 1]);
        $response = new Response(json_encode($lot, JSON_UNESCAPED_UNICODE));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/api/options", name="getOptionsConfig")
     */
    public function getOptionsConfig(): Response
    {
        $options = [];
        $data = Options::getAll();

        $options['maxEntryDate'] = $data[0]['VALUE'];
        $options['max_return_library'] = $data[1]['VALUE'];
        $options['max_return_bus'] = $data[2]['VALUE'];
        $options['max_return_library_lf'] = $data[3]['VALUE'];

        $response = new Response(json_encode($options, JSON_UNESCAPED_UNICODE));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/api/manual", name="getManualConfig")
     */
    public function getManualConfig(): Response
    {
        $data = Manual::get('link-ajudaClubs');

        $response = new Response(json_encode($data, JSON_UNESCAPED_UNICODE));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/api/optionsSave/{option1}/{option2}/{option3}/{option4}", name="saveOptionsConfig")
     */
    public function saveOptionsConfig(string $option1, string $option2, string $option3, string $option4)
    {
        $session = $this->container->get('session');
        $options = [];

        if (SecurityController::isAdmin($session)) {
            $options = Options::set($option1, $option2, $option3, $option4);
        }

        $response = new Response(json_encode($options, JSON_UNESCAPED_UNICODE));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/api/book/{id}", name="bookById")
     */
    public function bookById(int $id): Response
    {
        $api = new DibaApi();
        $book = $api->getBookFromCode($id);

        if (is_null($book)) {
            $response = new Response(json_encode([], JSON_UNESCAPED_UNICODE));
        } else {
            $response = new Response(json_encode($book->toArray(), JSON_UNESCAPED_UNICODE));
        }

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/api/cron", name="cron")
     */
    public function cron(): Response
    {
        // Update Libraries
        CronHelper::updateLibraries($this->container);

        $response = new Response(json_encode(['updated' => 1], JSON_UNESCAPED_UNICODE));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/file/{id}", name="downloadFile")
     */
    public function downloadFile(int $id)
    {
        $project_dir = $this->getParameter('kernel.project_dir');
        $path = preg_replace('/[^\/]+/', '..', $project_dir) . $_ENV['NAS_PATH'] . '/';
        $lot = $this->container->get('doctrine')->getRepository(Lots::class)->find(['id' => $id]);
        $year = Options::findFile($lot->getUrl(), $path);

        if ($year !== false) {
            $path .= $year . '/';
        }

        if (is_null($lot) || empty($lot->getUrl()) || !file_exists($path . $lot->getUrl())) {
            return new Response('', 404);
        }

        $file_path = $path . $lot->getUrl();

        $file_name = substr(basename($file_path), 27);

        //Define header information
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Pragma: public');

        //Clear system output buffer
        flush();

        //Read the size of the file
        readfile($file_path);

        //Terminate from the script
        die();
    }
}
