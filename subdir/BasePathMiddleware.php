<?php
// use RuntimeException;

use Psr\Http\Message\ {
    ServerRequestInterface as Request,
    ResponseInterface
};

use Psr\Http\Server\ {
    RequestHandlerInterface as RequestHandler,
    MiddlewareInterface
};

use Slim\App as SlimApp;

/**
 * @see {@link https://github.com/slimphp/Slim/issues/2512#issuecomment-519158042}
 */
class BasePathMiddleware implements MiddlewareInterface
{
    /** @var string */
    protected $basePath;

    /**
     * Constructor
     */
    public function __construct(string $basePath = null)
    {
        $this->basePath = $basePath;
    }

    /**
     * Configure slim application
     */
    public static function configureSlimApp(SlimApp $app, string $basePath): void
    {
        // Add middleware to resolve URLs without using broken App::setBasePath
        $app->add(new static($basePath));

        // Set router collector which is used for routes generation
        $routeCollector = $app->getRouteCollector();
        $routeCollector->setBasePath($basePath);
    }

    /**
     * @inheritdoc
     */
    public function process(Request $request, RequestHandler $handler): ResponseInterface
    {
        $basePath = $this->basePath ?? static::detectBasePath($request);

        $uri = $request->getUri();
        $path = $uri->getPath();

        // Strip base (don't use ltrim)
        $path = substr($path, strlen($basePath));

        // Update request
        $request = $request->withUri($uri->withPath($path));

        return $handler->handle($request);
    }

    /**
     * Detect base path from PATH_INFO
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return string
     * @throws \RuntimeException
     */
    public static function detectBasePath(Request $request): string
    {
        $serverParams = $request->getServerParams();

        // Cannot resolve
        if (!isset($serverParams['PATH_INFO'])) {
            throw new RuntimeException('Cannot resolve base path, PATH_INFO is missing');
        }

        $basePath = substr($serverParams['REQUEST_URI'], 0, -strlen($serverParams['PATH_INFO']));

        return $basePath;
    }

    /**
     * Detect base URI
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return string
     */
    public static function detectBaseUri(Request $request): string
    {
        $uri = $request->getUri();

        return $uri->getScheme() . '://' . $uri->getAuthority() . static::detectBasePath($request);
    }
}
