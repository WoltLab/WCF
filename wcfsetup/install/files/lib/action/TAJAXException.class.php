<?php
namespace wcf\action;
use wcf\system\exception\AJAXException;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\InvalidSecurityTokenException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\exception\ValidateActionException;
use wcf\system\WCF;

/**
 * Default implementation for the AJAXException throw method. 
 * 
 * @author	Alexander Ebert, Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Action
 * @since	5.2
 */
trait TAJAXException {
	/**
	 * Throws an previously caught exception while maintaining the propriate stacktrace.
	 *
	 * @param	\Exception|\Throwable	$e
	 * @throws	AJAXException
	 * @throws	\Exception
	 * @throws	\Throwable
	 */
	protected function throwException($e) {
		if ($e instanceof InvalidSecurityTokenException) {
			throw new AJAXException(
				WCF::getLanguage()->getDynamicVariable('wcf.ajax.error.sessionExpired'),
				AJAXException::SESSION_EXPIRED,
				$e->getTraceAsString(),
				[
					'file' => $e->getFile(),
					'line' => $e->getLine()
				]
			);
		}
		else if ($e instanceof PermissionDeniedException) {
			throw new AJAXException(
				WCF::getLanguage()->getDynamicVariable('wcf.ajax.error.permissionDenied'),
				AJAXException::INSUFFICIENT_PERMISSIONS,
				$e->getTraceAsString(),
				[
					'file' => $e->getFile(),
					'line' => $e->getLine()
				]
			);
		}
		else if ($e instanceof IllegalLinkException) {
			throw new AJAXException(
				WCF::getLanguage()->get('wcf.ajax.error.illegalLink'),
				AJAXException::ILLEGAL_LINK,
				$e->getTraceAsString(),
				[
					'file' => $e->getFile(),
					'line' => $e->getLine()
				]
			);
		}
		else if ($e instanceof UserInputException) {
			// repackage as ValidationActionException
			$exception = new ValidateActionException($e->getField(), $e->getType(), $e->getVariables());
			throw new AJAXException(
				$exception->getMessage(),
				AJAXException::BAD_PARAMETERS,
				$e->getTraceAsString(),
				[
					'errorMessage' => $exception->getMessage(),
					'errorType' => $e->getType(),
					'file' => $e->getFile(),
					'fieldName' => $exception->getFieldName(),
					'line' => $e->getLine(),
					'realErrorMessage' => $exception->getErrorMessage()
				]
			);
		}
		else if ($e instanceof ValidateActionException) {
			throw new AJAXException($e->getMessage(), AJAXException::BAD_PARAMETERS, $e->getTraceAsString(), [
				'errorMessage' => $e->getMessage(),
				'file' => $e->getFile(),
				'fieldName' => $e->getFieldName(),
				'line' => $e->getLine(),
				'realErrorMessage' => $e->getErrorMessage()
			]);
		}
		else if ($e instanceof NamedUserException) {
			throw new AJAXException(
				$e->getMessage(),
				AJAXException::BAD_PARAMETERS,
				$e->getTraceAsString(),
				[
					'file' => $e->getFile(),
					'line' => $e->getLine()
				]
			);
		}
		else {
			$returnValues = [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			];
			if ($e instanceof SystemException && $e->getDescription()) {
				$returnValues['description'] = $e->getDescription();
			}
			
			throw new AJAXException(
				$e->getMessage(),
				AJAXException::INTERNAL_ERROR,
				$e->getTraceAsString(),
				$returnValues,
				\wcf\functions\exception\logThrowable($e),
				$e->getPrevious()
			);
		}
	}
}
