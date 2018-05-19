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

class StatementRootNodeCannotBeMovedException extends Exception
{
    public function __construct($message = 'Statement root node cannot be moved.', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
