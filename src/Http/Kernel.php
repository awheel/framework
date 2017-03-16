<?php

namespace light\Http;

use Exception;
use light\App;
use light\Routing\Router;

/**
 * Http Kernel
 *
 * @package light\Http
 */
class Kernel
{
    /**
     * @var App
     */
    protected $app;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * HttpKernel constructor.
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * 处理 Request 请求
     *
     * @param Request $request
     *
     * @return Response
     * @throws Exception
     */
    public function handle(Request $request)
    {
        try {
            // 注册 Request
            $this->app->register('request', $request);

            // 启用 App
            $this->app->bootstrap();

            // 加载 Router
            $router = new Router();
            require $this->app->basePath.'/bootstrap/routes.php';

            // 注册 Router
            $this->app->register('router', $router);

            // 任务分发
            $response = $router->dispatch($request);
        }
        catch (Exception $e) {
            if ($e->getCode() != 404 && $e->getCode() != 405) {
                $this->app->make('log')->error('runtime exception '. $e->getMessage(), [
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            if ($this->app->configGet('app.debug')) {
                throw new Exception('Http Request handle error', $e->getCode(), $e);
            }

            // todo render exception
            $response = null;
        }

        return $response;
    }

    /**
     * 结束
     *
     * @param Request $request
     * @param Response $response
     *
     * @return bool
     */
    public function terminate(Request $request, Response $response)
    {
        // todo 运行 register_shutdown_functions 注册的方法

        return true;
    }
}
