<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 10.05.2018
 * Time: 18:17
 */

namespace ExpertSystem\Exception;

use Exception;
use Throwable;

class StatementRootNodeAlreadyExistsException extends Exception
{
    public function __construct($message = 'Root statement already exists.', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
