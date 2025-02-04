<?php

declare(strict_types=1);

use App\Auth;
use App\Csrf;
use Psr\SimpleCache\CacheInterface;
use Slim\App;
use App\Config;
use App\Session;
use Slim\Csrf\Guard;
use Slim\Interfaces\RouteParserInterface;
use Slim\Views\Twig;
use App\Enum\SameSite;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;
use function DI\create;
use Clockwork\Clockwork;
use Doctrine\ORM\ORMSetup;
use App\Enum\StorageDriver;
use App\Filters\UserFilter;
use App\Enum\AppEnvironment;
use Slim\Factory\AppFactory;
use Doctrine\ORM\EntityManager;
use App\Contracts\AuthInterface;
use Doctrine\DBAL\DriverManager;
use League\Flysystem\Filesystem;
use App\DataObjects\SessionConfig;
use Clockwork\Storage\FileStorage;
use Twig\Extra\Intl\IntlExtension;
use App\Contracts\SessionInterface;
use App\RouteEntityBindingStrategy;
use Symfony\Component\Asset\Package;
use Symfony\Component\Mailer\Mailer;
use App\Services\UserProviderService;
use Psr\Container\ContainerInterface;
use Symfony\Component\Asset\Packages;
use App\Services\EntityManagerService;
use Symfony\Component\Mailer\Transport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Clockwork\DataSource\DoctrineDataSource;
use Symfony\Component\Mailer\MailerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use App\Contracts\UserProviderServiceInterface;
use App\Contracts\EntityManagerServiceInterface;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Component\Mime\BodyRendererInterface;
use App\RequestValidators\RequestValidatorFactory;
use League\Flysystem\Local\LocalFilesystemAdapter;

use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use App\Contracts\RequestValidatorFactoryInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Twig\EntryFilesTwigExtension;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;


return [
    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);

        $router = require CONFIG_PATH . "/routes/web.php";
        $addMiddleware = require CONFIG_PATH . '/middleware.php';

        $app = AppFactory::create();

        $app->getRouteCollector()->setDefaultInvocationStrategy(
            new RouteEntityBindingStrategy(
                $container->get(EntityManagerServiceInterface::class),
                $app->getResponseFactory()
            )
        );

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
    EntityManagerInterface::class => function (Config $config) {
        $configure = ORMSetup::createAttributeMetadataConfiguration(
            paths: $config->get('doctrine.entity_dir'),
            isDevMode: $config->get('doctrine.dev_mode'),
        );

        $configure->addFilter('user', UserFilter::class);

        return new EntityManager(
            DriverManager::getConnection($config->get('doctrine.connection'), $configure),
            $configure,
        );
    },
    'webpack_encore.packages' => fn() => new Packages(
        new Package(new JsonManifestVersionStrategy(
            BUILD_PATH . '/manifest.json'
        ))
    ),
    'webpack_encore.entrypoint_lookup' => fn() => new EntrypointLookup(BUILD_PATH . '/entrypoints.json'),
    'webpack_encore.tag_renderer' => fn(ContainerInterface $container) => new TagRenderer(
        new EntrypointLookupCollection($container),
        $container->get('webpack_encore.packages')
    ),
    '_default' => fn() => new EntrypointLookup(BUILD_PATH . '/entrypoints.json'),

    ResponseFactoryInterface::class => fn(App $app) => $app->getResponseFactory(),
    AuthInterface::class => fn(ContainerInterface $container) => $container->get(Auth::class),
    UserProviderServiceInterface::class => fn(ContainerInterface $container) => $container->get(UserProviderService::class),
    SessionInterface::class => fn(Config $config) => new Session(
        new SessionConfig(
            $config->get('session.name', ''),
            $config->get('session.flashName', 'flash'),
            $config->get('session.secure', true),
            $config->get('session.httponly', true),
            SameSite::from($config->get('session.samesite', 'lax')),
        )
    ),
    RequestValidatorFactoryInterface::class => fn(ContainerInterface $container) => $container->get(RequestValidatorFactory::class),
    'csrf' => fn(ResponseFactoryInterface $responseFactory, Csrf $csrf) => new Guard(
        $responseFactory,
        failureHandler: $csrf->failureHandler(),
        persistentTokenMode: true
    ),
    Filesystem::class => function (Config $config) {
        $digitalOcean = function (array $options) {
            $client = new Aws\S3\S3Client(
                [
                    'credentials' => [
                        'key' => $options['key'],
                        'secret' => $options['secret'],
                    ],
                    'region' => $options['region'],
                ]
            );

            return new League\Flysystem\AwsS3V3\AwsS3V3Adapter(
                $client,
                $options['bucket']
            );
        };

        $adapter = match ($config->get('storage.driver')) {
            StorageDriver::Local => new LocalFilesystemAdapter(STORAGE_PATH),
            StorageDriver::Remote_AWS => $digitalOcean($config->get('storage.s3')),
        };

        return new League\Flysystem\Filesystem($adapter);
    },

    Clockwork::class => function (EntityManagerInterface $entityManager, Config $config) {
        $clockwork = new Clockwork();

        // new DoctrineDataSource($entityManager);
    
        $clockwork->storage(new FileStorage(STORAGE_PATH . '/clockwork'));
        // $clockwork->addDataSource(new DoctrineDataSource($entityManager));
        $clockwork->addDataSource($dataSource = new DoctrineDataSource($entityManager));
        $configure = ORMSetup::createAttributeMetadataConfiguration(
            paths: $config->get('doctrine.entity_dir'),
            isDevMode: $config->get('doctrine.dev_mode'),
        );
        $config = $dataSource->configure($configure);

        return $clockwork;
    },
    EntityManagerServiceInterface::class => fn(EntityManagerInterface $entityManager) => new EntityManagerService(
        $entityManager
    ),

    MailerInterface::class => function (Config $config) {
        $transport = Transport::fromDsn($config->get('mailer.dsn'));

        return new Mailer($transport);
    },

    BodyRendererInterface::class => fn(Twig $twig) => new BodyRenderer($twig->getEnvironment()),
    RouteParserInterface::class => fn(App $app) => $app->getRouteCollector()->getRouteParser(),
    CacheInterface::class => function(Config $config) {
        $redis = new \Redis();
        $config = $config->get('redis');

        $redis->connect($config['host'], (int) $config['port']);
        $redis->auth($config['password']);

        $adapter = new RedisAdapter($redis);

        return new Psr16Cache($adapter);
    }
];