/**
 * Automatic URL rewrite support testing.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Option/RewriteTest
 */
define(['AjaxRequest', 'Language', 'Ui/Dialog'], function (AjaxRequest, Language, UiDialog) {
	"use strict";
	
	var _buttonStartTest = elById('rewriteTestStart');
	var _callbackChange = null;
	var _option = elById('url_omit_index_php');
	var _testPassed = false;
	var _testUrl = '';
	
	/**
	 * @exports     WoltLabSuite/Core/Acp/Ui/Option/RewriteTest
	 */
	return {
		/**
		 * Initializes the rewrite test, but aborts early if URL rewriting was
		 * enabled at page init.
		 * 
		 * @param       {string}        testUrl
		 */
		init: function (testUrl) {
			if (_option.checked) {
				// option is already enabled, ignore it
				return;
			}
			
			_callbackChange = this.onChange.bind(this);
			_option.addEventListener('change', _callbackChange);
			_testUrl = testUrl;
		},
		
		/**
		 * Forces the rewrite test when attempting to enable the URL rewriting.
		 * 
		 * @param       {Event}         event
		 */
		onChange: function (event) {
			event.preventDefault();
			
			UiDialog.open(this);
		},
		
		/**
		 * Runs the actual rewrite test.
		 * 
		 * @param       {Event?}        event
		 * @protected
		 */
		_runTest: function (event) {
			if (event instanceof Event) event.preventDefault();
			
			if (_buttonStartTest.disabled) return;
			
			_buttonStartTest.disabled = true;
			this._setStatus('running');
			
			var failure = (function () {
				window.setTimeout((function() {
					_buttonStartTest.disabled = false;
					
					this._setStatus('failure');
				}).bind(this), 500);
			}).bind(this);
			
			var request = new AjaxRequest({
				ignoreError: true,
				// bypass the LinkHandler, because rewrites aren't enabled yet
				url: _testUrl,
				success: (function (data) {
					if (!data.hasOwnProperty('core_rewrite_test') || data.core_rewrite_test !== 'passed') {
						failure();
						return;
					}
					
					window.setTimeout((function() {
						_testPassed = true;
						
						this._setStatus('success');
						
						_option.removeEventListener('change', _callbackChange);
						
						window.setTimeout((function () {
							if (UiDialog.isOpen(this)) {
								UiDialog.close(this);
							}
						}).bind(this), 1000);
					}).bind(this), 500);
				}).bind(this),
				
				failure: failure
			});
			request.sendRequest(false);
		},
		
		/**
		 * Displays the appropriate dialog message.
		 * 
		 * @param       {string}        status
		 * @protected
		 */
		_setStatus: function (status) {
			var containers = [
				elById('dialogRewriteTestRunning'),
				elById('dialogRewriteTestSuccess'),
				elById('dialogRewriteTestFailure')
			];
			
			containers.forEach(elHide);
			
			var i = 0;
			if (status === 'success') i = 1;
			else if (status === 'failure') i = 2;
			
			elShow(containers[i]);
		},
		
		_dialogSetup: function () {
			return {
				id: 'dialogRewriteTest',
				options: {
					onClose: function () {
						if (!_testPassed) elById('url_omit_index_php_no').checked = true;
					},
					onSetup: (function () {
						_buttonStartTest.addEventListener(WCF_CLICK_EVENT, this._runTest.bind(this));
					}).bind(this),
					onShow: this._runTest.bind(this),
					silent: true,
					title: Language.get('wcf.acp.option.url_omit_index_php')
				}
			};
		}
	};
});
