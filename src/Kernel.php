<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Symfony\Component\Yaml\Yaml;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * @inheritdoc
     */
    public function registerBundles()
    {
        return [
            new FrameworkBundle()
        ];
    }

    public function getCacheDir()
    {
        return $this->getRootDir() . "/../var/cache/{$this->getEnvironment()}";
    }

    public function getLogDir()
    {
        return $this->getRootDir() . '/../var/logs/';
    }

    /**
     * @inheritdoc
     */
    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->add('/encode', 'kernel:encodeAction');
        $routes->add('/decode', 'kernel:decodeAction');
        $routes->add('/docs', 'kernel:docsAction');
    }

    /**
     * @inheritdoc
     */
    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->setParameter('secret', getenv('SECRET'));
        $c->loadFromExtension('framework', array(
            'secret' => $c->getParameter('secret'),
            'test' => true,
        ));
        $c->addDefinitions([
            Encrypt::class => new Definition(Encrypt::class),
            Session::class => new Definition(Session::class, [
                new Reference(Encrypt::class)
            ]),
            JsonRequestSubscriber::class => new Definition(JsonRequestSubscriber::class)
        ]);

        $c->getDefinition(JsonRequestSubscriber::class)->addTag('kernel.event_subscriber');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function encodeAction(Request $request)
    {
        $cookieData = $request->get('data', false);
        $configuration = $request->get('config', []);

        if (!$cookieData) {
            return Response::create('Empty Request', Response::HTTP_BAD_REQUEST);
        }

        $sessionConfig = SessionConfiguration::fromArray($configuration);

        $encoded = $this->getContainer()
            ->get(Session::class)
            ->encode($sessionConfig, $cookieData);

        return Response::create($encoded);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function decodeAction(Request $request)
    {
        $cookie = $request->get('cookie', false);
        $configuration = $request->get('config', []);

        if (!$cookie) {
            return Response::create('Missing cookie param', Response::HTTP_BAD_REQUEST);
        }

        $sessionConfig = SessionConfiguration::fromArray($configuration);

        $this->getContainer()
            ->get(Encrypt::class)
            ->setEncryptionKey($sessionConfig->encryption_key);

        $decoded = $this->getContainer()
            ->get(Session::class)
            ->decode($sessionConfig, $cookie);

        return JsonResponse::create($decoded);
    }

    /**
     * @return Response
     */
    public function docsAction()
    {
        $docs = Yaml::parseFile(__DIR__ . '/docs.yml');

        return JsonResponse::create($docs, Response::HTTP_OK, [
            'Access-Control-Allow-Origin' => '*'
        ]);
    }
}
