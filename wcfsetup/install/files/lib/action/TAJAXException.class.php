<?php

namespace wcf\action;

use wcf\http\error\ExceptionLogger;
use wcf\system\exception\AJAXException;
use wcf\system\exception\IExtraInformationException;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\InvalidSecurityTokenException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\exception\ValidateActionException;
use wcf\util\JSON;

/**
 * Default implementation for the AJAXException throw method.
 *
 * @author  Alexander Ebert, Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
trait TAJAXException
{
    /**
     * Throws an previously caught exception while maintaining the propriate stacktrace.
     *
     * @param \Exception|\Throwable $e
     * @return never
     * @throws AJAXException
     * @throws \Exception
     * @throws \Throwable
     */
    protected function throwException($e)
    {
        if ($e instanceof InvalidSecurityTokenException) {
            throw $e;
        } elseif ($e instanceof PermissionDeniedException) {
            throw $e;
        } elseif ($e instanceof IllegalLinkException) {
            throw $e;
        } elseif ($e instanceof UserInputException) {
            // repackage as ValidationActionException
            $exception = new ValidateActionException($e->getField(), $e->getType(), $e->getVariables());
            $ajaxException = new AJAXException(
                $exception->getMessage(),
                AJAXException::BAD_PARAMETERS,
                $e->getTraceAsString(),
                [
                    'errorMessage' => $exception->getMessage(),
                    'errorType' => $e->getType(),
                    'file' => $e->getFile(),
                    'fieldName' => $exception->getFieldName(),
                    'line' => $e->getLine(),
                    'realErrorMessage' => $exception->getErrorMessage(),
                ]
            );
            self::sendAjaxResponse($ajaxException);
        } elseif ($e instanceof ValidateActionException) {
            $ajaxException = new AJAXException($e->getMessage(), AJAXException::BAD_PARAMETERS, $e->getTraceAsString(), [
                'errorMessage' => $e->getMessage(),
                'file' => $e->getFile(),
                'fieldName' => $e->getFieldName(),
                'line' => $e->getLine(),
                'realErrorMessage' => $e->getErrorMessage(),
            ]);
            self::sendAjaxResponse($ajaxException);
        } elseif ($e instanceof NamedUserException) {
            $ajaxException = new AJAXException(
                $e->getMessage(),
                AJAXException::BAD_PARAMETERS,
                AJAXException::getSanitizedTraceAsString($e),
                [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            );
            self::sendAjaxResponse($ajaxException);
        } else {
            $returnValues = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
            if ($e instanceof SystemException && $e->getDescription()) {
                $returnValues['description'] = $e->getDescription();
            }

            $extraInformation = ($e instanceof IExtraInformationException) ? $e->getExtraInformation() : [];

            $ajaxException = new AJAXException(
                $e->getMessage(),
                AJAXException::INTERNAL_ERROR,
                AJAXException::getSanitizedTraceAsString($e),
                $returnValues,
                ExceptionLogger::log($e),
                $e->getPrevious(),
                $extraInformation,
            );
            self::sendAjaxResponse($ajaxException);
        }
    }

    /**
     * Sends the JSON response for the given AJAXException and terminates the request.
     *
     * @return never
     */
    private static function sendAjaxResponse(AJAXException $exception): never
    {
        \header($exception->getStatusHeader());
        \header('Content-type: application/json; charset=UTF-8');
        echo JSON::encode($exception->getResponseData());

        exit;
    }
}
