<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 10.05.2018
 * Time: 18:17
 */

namespace ExpertSystem\Handler;

use Exception;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ExceptionHandler implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['exception_handle'] = $app->protect(function (Exception $exception) use ($app) {
            return (new JsonResponse())->setData(['error' => $exception->getMessage()]);
        });
    }
}
