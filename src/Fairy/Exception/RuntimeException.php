<?php
/**
 * Runtime Call Exception
 *
 */
namespace  Fairy\Exception;

use Exception;
use RuntimeException as Runtime;

/**
 * Runtime Exception
 *
 * @package    Exception
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @since      1.0
 */
class RuntimeException extends Runtime implements ExceptionInterface
{
    /**
     * Class Constructor
     *
     * @param   null|string $message
     * @param   null|int    $code
     * @param   Exception   $previous
     *
     * @link    http://php.net/manual/en/exception.construct.php
     * @link    http://php.net/manual/en/language.exceptions.extending.php
     * @since   1.0.0
     */
    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Custom String Representation of Object
     *
     * @return  string
     * @since   1.0.0
     */
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
