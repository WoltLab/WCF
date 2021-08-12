<?php

/**
 * @see       https://github.com/laminas/laminas-progressbar for the canonical source repository
 * @copyright https://github.com/laminas/laminas-progressbar/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-progressbar/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ProgressBar\Adapter\Exception;

use Laminas\ProgressBar\Exception;

/**
 * Exception for Laminas\Progressbar component.
 */
class InvalidArgumentException extends Exception\InvalidArgumentException implements ExceptionInterface
{
}
