<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 10.05.2018
 * Time: 18:17
 */

namespace ExpertSystem\Handler;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResponseHandler implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['response_handle'] = $app->protect(function ($data) use ($app) {
            return (new JsonResponse())->setData($data);
        });
    }
}
