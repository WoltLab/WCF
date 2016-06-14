<?php
namespace wcf\action;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\exception\SystemException;
use wcf\system\payment\type\IPaymentType;
use wcf\util\HTTPRequest;
use wcf\util\StringUtil;

/**
 * Handles Paypal callbacks.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Action
 */
class PaypalCallbackAction extends AbstractAction {
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		// check response
		$processor = null;
		try {
			// post back to paypal to validate 
			/** @noinspection PhpUnusedLocalVariableInspection */
			$content = '';
			try {
				$url = 'https://www.paypal.com/cgi-bin/webscr';
				if (!empty($_POST['test_ipn'])) {
					// IPN simulator notification
					$url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
				}
				
				$request = new HTTPRequest($url, [], array_merge(['cmd' => '_notify-validate'], $_POST));
				$request->execute();
				$reply = $request->getReply();
				$content = $reply['body'];
			}
			catch (SystemException $e) {
				throw new SystemException('connection to paypal.com failed: ' . $e->getMessage());
			}
			
			if (strstr($content, "VERIFIED") === false) {
				throw new SystemException('request not validated');
			}
			
			// fix encoding
			if (!empty($_POST['charset']) && strtoupper($_POST['charset']) != 'UTF-8') {
				foreach ($_POST as &$value) {
					$value = StringUtil::convertEncoding(strtoupper($_POST['charset']), 'UTF-8', $value);
				}
			}
			
			// Check that receiver_email is your Primary PayPal email
			if (strtolower($_POST['business']) != strtolower(PAYPAL_EMAIL_ADDRESS) && (strtolower($_POST['receiver_email']) != strtolower(PAYPAL_EMAIL_ADDRESS))) {
				throw new SystemException('invalid business or receiver_email');
			}
			
			// get token
			if (!isset($_POST['custom'])) {
				throw new SystemException('invalid custom item');
			}
			$tokenParts = explode(':', $_POST['custom'], 2);
			if (count($tokenParts) != 2) {
				throw new SystemException('invalid custom item');
			}
			// get payment type object type
			$objectType = ObjectTypeCache::getInstance()->getObjectType(intval($tokenParts[0]));
			if ($objectType === null || !($objectType->getProcessor() instanceof IPaymentType)) {
				throw new SystemException('invalid payment type id');
			}
			$processor = $objectType->getProcessor();
			
			// get status
			$transactionType = (!empty($_POST['txn_type']) ? $_POST['txn_type'] : '');
			$paymentStatus = (!empty($_POST['payment_status']) ? $_POST['payment_status'] : '');
			
			$status = '';
			if ($transactionType == 'web_accept' || $transactionType == 'subscr_payment') {
				if ($paymentStatus == 'Completed') {
					$status = 'completed';
				}
			}
			if ($paymentStatus == 'Refunded' || $paymentStatus == 'Reversed') {
				$status = 'reversed';
			}
			if ($paymentStatus == 'Canceled_Reversal') {
				$status = 'canceled_reversal';
			}
			
			if ($status) {
				$processor->processTransaction(ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.payment.method', 'com.woltlab.wcf.payment.method.paypal'), $tokenParts[1], $_POST['mc_gross'], $_POST['mc_currency'], $_POST['txn_id'], $status, $_POST);
			}
		}
		catch (SystemException $e) {
			@header('HTTP/1.1 500 Internal Server Error');
			echo $e->getMessage();
			exit;
		}
	}
}
