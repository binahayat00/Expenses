<?php

declare(strict_types=1);

use App\Auth;
use Slim\App;
use App\Config;
use Slim\Views\Twig;
use function DI\create;
use Doctrine\ORM\ORMSetup;
use App\Enum\AppEnvironment;
use Slim\Factory\AppFactory;
use Doctrine\ORM\EntityManager;
use App\Contracts\AuthInterface;
use Doctrine\DBAL\DriverManager;
use Twig\Extra\Intl\IntlExtension;
use Symfony\Component\Asset\Package;
use App\Services\UserProviderService;
use Psr\Container\ContainerInterface;
use Symfony\Component\Asset\Packages;
use Psr\Http\Message\ResponseFactoryInterface;
use App\Contracts\UserProviderServiceInterface;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Twig\EntryFilesTwigExtension;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;

return [
    App::class => function(ContainerInterface $container){
        AppFactory::setContainer($container);
        
        $router = require CONFIG_PATH ."/routes/web.php";
        $addMiddleware = require CONFIG_PATH . '/middleware.php';

        $app = AppFactory::create();

        $router($app);

        $addMiddleware($app);
        
        return $app;
    },
    Config::class => create(Config::class)->constructor(require CONFIG_PATH . "/app.php"),
    Twig::class => function (Config $config, ContainerInterface $container) {
        $twig = Twig::create(VIEW_PATH, [
            'cache' => STORAGE_PATH . '/cache/templates',
            'auto_reload' => AppEnvironment::isDevelopment($config->get('app_environment')),
        ]);

        $twig->addExtension(new IntlExtension());
        $twig->addExtension(new EntryFilesTwigExtension($container));
        $twig->addExtension(new AssetExtension($container->get('webpack_encore.packages')));

        return $twig;
    },
    EntityManager::class =>  function(Config $config) {
        $configure = ORMSetup::createAttributeMetadataConfiguration(
            paths: $config->get('doctrine.entity_dir'),
            isDevMode: $config->get('doctrine.dev_mode'),
        );
        
        return new EntityManager(
        DriverManager::getConnection($config->get('doctrine.connection'), $configure),
        $configure,
        );
    },
    'webpack_encore.packages' => fn() => new Packages(
        new Package(new JsonManifestVersionStrategy(
            BUILD_PATH . '/manifest.json'
    ))),
    'webpack_encore.entrypoint_lookup' => fn() => new EntrypointLookup(BUILD_PATH . '/entrypoints.json'),
    'webpack_encore.tag_renderer' => fn(ContainerInterface $container) => new TagRenderer(
        new EntrypointLookupCollection($container),
        $container->get('webpack_encore.packages')
    ),
    '_default' => fn() => new EntrypointLookup(BUILD_PATH . '/entrypoints.json'),

    ResponseFactoryInterface::class => fn(App $app) => $app->getResponseFactory(),
    AuthInterface::class => fn(ContainerInterface $container) => $container->get(Auth::class),
    UserProviderServiceInterface::class => fn(ContainerInterface $container) => $container->get(UserProviderService::class),
];