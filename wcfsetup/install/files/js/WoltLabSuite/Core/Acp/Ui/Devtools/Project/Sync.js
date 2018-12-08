define(['Ajax', 'Dictionary', 'Language', 'Ui/Dialog'], function (Ajax, Dictionary, Language, UiDialog) {
	"use strict";
	
	var _buttons = new Dictionary();
	var _buttonStatus = new Dictionary();
	var _buttonSyncAll = null;
	var _container = elById('syncPipMatches');
	var _pips = [];
	var _projectId = 0;
	var _queue = [];
	
	return {
		init: function (projectId) {
			_projectId = projectId;
			
			
			elById('syncShowOnlyMatches').addEventListener('change', function() {
				_container.classList.toggle('jsShowOnlyMatches');
			});
			
			var existingPips = [], knownPips = [], tmpPips = [];
			elBySelAll('.jsHasPipTargets:not(.jsSkipTargetDetection)', _container, (function (pip) {
				var pluginName = elData(pip, 'plugin-name');
				var targets = [];
				
				elBySelAll('.jsHasPipTargets[data-plugin-name="' + pluginName + '"] .jsInvokePip', _container, (function(button) {
					var target = elData(button, 'target');
					targets.push(target);
					
					button.addEventListener(WCF_CLICK_EVENT, (function(event) {
						event.preventDefault();
						
						if (_queue.length > 0) return;
						
						this._sync(pluginName, target);
					}).bind(this));
					
					_buttons.set(pluginName + '-' + target, button);
					_buttonStatus.set(pluginName + '-' + target, elBySel('.jsHasPipTargets[data-plugin-name="' + pluginName + '"] .jsInvokePipResult[data-target="' + target + '"]', _container));
				}).bind(this));
				
				var data = {
					dependencies: JSON.parse(elData(pip, 'sync-dependencies')),
					pluginName: pluginName,
					targets: targets
				};
				
				if (data.dependencies.length > 0) {
					tmpPips.push(data);
				}
				else {
					_pips.push(data);
					knownPips.push(pluginName);
				}
				
				existingPips.push(pluginName);
			}).bind(this));
			
			var resolvedDependency = false;
			while (tmpPips.length > 0) {
				resolvedDependency = false;
				
				var openDependencies, item, length = tmpPips.length;
				for (var i = 0; i < length; i++) {
					item = tmpPips[i];
					
					openDependencies = item.dependencies.filter(function (dependency) {
						// Ignore any dependencies that are not present.
						if (existingPips.indexOf(dependency) === -1) {
							window.console.info('The dependency "' + dependency + '" does not exist and has been ignored.');
							return false;
						}
						
						return (knownPips.indexOf(dependency) === -1);
					});
					
					if (openDependencies.length === 0) {
						knownPips.push(item.pluginName);
						_pips.push(item);
						tmpPips.splice(i, 1);
						
						resolvedDependency = true;
						break;
					}
				}
				
				if (!resolvedDependency) {
					// We could not resolve any dependency, either because there is no more pip
					// in `tmpPips` or we're facing a circular dependency. In case there are items
					// left, we simply append them to the end and hope for the operation to
					// complete anyway, despite unmatched dependencies.
					tmpPips.forEach(function(pip) {
						window.console.warn('Unable to resolve dependencies for', pip);
						
						_pips.push(pip);
					});
					
					break;
				}
			}
			
			var syncAll = elCreate('li');
			syncAll.innerHTML = '<a href="#" class="button"><span class="icon icon16 fa-refresh"></span> ' + Language.get('wcf.acp.devtools.sync.syncAll') + '</a>';
			_buttonSyncAll = syncAll.children[0];
			_buttonSyncAll.addEventListener(WCF_CLICK_EVENT, this._syncAll.bind(this));
			
			var list = elBySel('.contentHeaderNavigation > ul');
			list.insertBefore(syncAll, list.firstElementChild);
		},
		
		_sync: function (pluginName, target) {
			_buttons.get(pluginName + '-' + target).disabled = true;
			_buttonStatus.get(pluginName + '-' + target).innerHTML = '<span class="icon icon16 fa-spinner"></span>';
			
			Ajax.api(this, {
				parameters: {
					pluginName: pluginName,
					target: target
				}
			});
		},
		
		_syncAll: function (event) {
			event.preventDefault();
			
			if (_buttonSyncAll.classList.contains('disabled')) {
				return;
			}
			
			_buttonSyncAll.classList.add('disabled');
			
			_queue = [];
			_pips.forEach(function(pip) {
				pip.targets.forEach(function (target) {
					_queue.push([pip.pluginName, target]);
				});
			});
			this._syncNext();
		},
		
		_syncNext: function () {
			if (_queue.length === 0) {
				_buttonSyncAll.classList.remove('disabled');
				
				// TODO: do stuff
				return;
			}
			
			var next = _queue.shift();
			this._sync(next[0], next[1]);
		},
		
		_ajaxSuccess: function(data) {
			_buttons.get(data.returnValues.pluginName + '-' + data.returnValues.target).disabled = false;
			_buttonStatus.get(data.returnValues.pluginName + '-' + data.returnValues.target).innerHTML = data.returnValues.timeElapsed;
			
			this._syncNext();
		},
		
		_ajaxFailure: function (data, responseText, xhr, requestData) {
			_buttons.get(requestData.parameters.pluginName + '-' + requestData.parameters.target).disabled = false;
			
			var buttonStatus = _buttonStatus.get(requestData.parameters.pluginName + '-' + requestData.parameters.target);
			buttonStatus.innerHTML = '<a href="#">' + Language.get('wcf.acp.devtools.sync.status.failure') + '</a>';
			buttonStatus.children[0].addEventListener(WCF_CLICK_EVENT, (function (event) {
				event.preventDefault();
				
				UiDialog.open(
					this,
					Ajax.getRequestObject(this).getErrorHtml(data, xhr)
				);
			}).bind(this));
			
			_buttonSyncAll.classList.remove('disabled');
		},
		
		_ajaxSetup: function () {
			return {
				data: {
					actionName: 'invoke',
					className: 'wcf\\data\\package\\installation\\plugin\\PackageInstallationPluginAction',
					parameters: {
						projectID: _projectId
					}
				}
			}
		},
		
		_dialogSetup: function() {
			return {
				id: 'devtoolsProjectSyncPipError',
				options: {
					title: Language.get('wcf.global.error.title')
				},
				source: null
			}
		}
	};
});
