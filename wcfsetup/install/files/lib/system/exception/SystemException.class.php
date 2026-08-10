<?php

namespace wcf\system\exception;

/**
 * A SystemException is thrown when an unexpected error occurs.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class SystemException extends LoggedException implements IExtraInformationException
{
    /**
     * error description
     * @var string
     */
    protected $description;

    /**
     * additional information
     * @var string
     */
    protected $information = '';

    /**
     * additional information
     * @var string
     */
    protected $functions = '';

    /**
     * @param string $message error message
     * @param int $code error code
     * @param string $description description of the error
     * @param \Exception $previous repacked Exception
     */
    public function __construct(string $message = '', int $code = 0, string $description = '', ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->description = $description;
    }

    /**
     * Returns the description of this exception.
     *
     * @return  string
     */
    public function getDescription()
    {
        return $this->description;
    }

    #[\Override]
    public function getExtraInformation()
    {
        if ($this->description !== '') {
            return [
                ['Description', $this->description],
            ];
        }

        return [];
    }

    /**
     * @return void
     */
    public function show() {}
}
