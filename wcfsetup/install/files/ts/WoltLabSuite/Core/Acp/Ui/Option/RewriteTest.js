/**
 * Automatic URL rewrite support testing.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Option/RewriteTest
 */
define(['AjaxRequest', 'Language', 'Ui/Dialog'], function (AjaxRequest, Language, UiDialog) {
	"use strict";
	
	var _apps;
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
		 * @param       {Dictionary}    apps
		 */
		init: function (apps) {
			// This configuration part is unavailable when running in enterprise mode.
			if (_option === null) {
				return;
			}
			
			if (_option.checked) {
				// option is already enabled, ignore it
				return;
			}
			
			_callbackChange = this.onChange.bind(this);
			_option.addEventListener('change', _callbackChange);
			_apps = apps;
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
			
			var tests = [];
			_apps.forEach(function (url, app) {
				tests.push(new Promise(function (resolve, reject) {
					var failure = function() {
						reject({ app: app, pass: false });
					};
					
					var request = new AjaxRequest({
						ignoreError: true,
						// bypass the LinkHandler, because rewrites aren't enabled yet
						url: url,
						type: 'GET',
						includeRequestedWith: false,
						success: function(data) {
							if (!data.hasOwnProperty('core_rewrite_test') || data.core_rewrite_test !== 'passed') {
								failure();
							}
							else {
								resolve({app: app, pass: true});
							}
						},
						failure: failure
					});
					request.sendRequest(false);
				}));
			});
			
			Promise.all(tests.map(function(test) {
				// wait for all promises, even if some are rejected
				// this will also cause `then()` to be always called
				return test.catch(function(result) {
					return result;
				});
			})).then((function(results) {
				var passed = true;
				results.forEach(function(result) {
					if (!result.pass) {
						passed = false;
					}
				});
				
				window.setTimeout((function() {
					if (passed) {
						_testPassed = true;
						
						this._setStatus('success');
						
						_option.removeEventListener('change', _callbackChange);
						
						window.setTimeout((function () {
							if (UiDialog.isOpen(this)) {
								UiDialog.close(this);
							}
						}).bind(this), 1000);
					}
					else {
						_buttonStartTest.disabled = false;
						
						var html = '';
						results.forEach(function(result) {
							html += '<li><span class="badge label ' + (result.pass ? 'green' : 'red') + '">' + Language.get('wcf.acp.option.url_omit_index_php.test.status.' + (result.pass ? 'success' : 'failure')) + '</span> ' + result.app + '</li>';
						});
						elById('dialogRewriteTestFailureResults').innerHTML = html;
						
						this._setStatus('failure');
					}
				}).bind(this), 500);
			}).bind(this));
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
						_buttonStartTest.addEventListener('click', this._runTest.bind(this));
					}).bind(this),
					onShow: this._runTest.bind(this),
					silent: true,
					title: Language.get('wcf.acp.option.url_omit_index_php')
				}
			};
		}
	};
});
