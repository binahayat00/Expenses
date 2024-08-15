<?php

declare(strict_types=1);

use App\Config;
use Slim\Views\Twig;
use function DI\create;
use Doctrine\ORM\ORMSetup;
use App\Enum\AppEnvironment;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;
use Twig\Extra\Intl\IntlExtension;
use Symfony\Component\Asset\Package;
use Psr\Container\ContainerInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Twig\EntryFilesTwigExtension;

use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface;

return [
    Config::class => create(Config::class)->constructor(require CONFIG_PATH . "/app.php"),
    Twig::class => function (Config $config, ContainerInterface $container) {
        // $twig = Twig::create(VIEW_PATH, [
        //     'cache' => STORAGE_PATH . '/cache/templates',
        //     'auto_reload' => AppEnvironment::isDevelopment($config->get('app_environment')),
        // ]);
        $twig = Twig::create(VIEW_PATH, [
            'cache' => STORAGE_PATH . '/cache/templates',
            'auto_reload' => AppEnvironment::isDevelopment($config->get('app_environment')),
        ]);
        
        // $loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) ."/../resources/assets/views");
        // $twig = new \Twig\Environment($loader, [
        //     'cache' => STORAGE_PATH . '/cache/templates',
        // ]);

        $twig->addExtension(new IntlExtension());
        $twig->addExtension(new EntryFilesTwigExtension($container));
        $twig->addExtension(new AssetExtension($container->get('webpack_encore.packages')));
        // $twig->addExtension(new AssetExtension($container->get('webpack_encore.packages')));
        
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
    // 'webpack_encore.packages' => fn() => new Packages(
    //     new Package(new JsonManifestVersionStrategy(
    //         BUILD_PATH . '/manifest.json'
    // ))),
    'webpack_encore.entrypoint_lookup' => fn() => new EntrypointLookup(BUILD_PATH . '/entrypoints.json'),
    // 'webpack_encore.tag_renderer' => fn(ContainerInterface $container) => new TagRenderer(
    //     new EntrypointLookupCollection($container->get('webpack_encore.entrypoint_lookup')),
    //     $container->get('webpack_encore.packages')
    // ),
    'webpack_encore.tag_renderer' => fn(ContainerInterface $container) => new TagRenderer(
        new EntrypointLookupCollection($container),//->get('webpack_encore.entrypoint_lookup')
        // $container->get('webpack_encore.packages')
        $container->get('webpack_encore.packages')
    ),
    // 'webpack_encore.tag_renderer' => fn(ContainerInterface $container) => new TagRenderer(
        // new EntrypointLookup(BUILD_PATH . '/entrypoints.json'),
    //     $container->get('webpack_encore.packages')
    // ),
    '_default' => fn() => new EntrypointLookup(BUILD_PATH . '/entrypoints.json'),
];