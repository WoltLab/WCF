/**
 * Developer tools for WoltLab Suite.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Devtools
 */
define([], function() {
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		return {
			help: function () {},
			toggleEditorAutosave: function () {},
			toggleEventLogging: function () {},
			_internal_: {
				enable: function () {},
				editorAutosave: function () {},
				eventLog: function() {}
			}
		};
	}
	
	var _settings = {
		editorAutosave: true,
		eventLogging: false
	};
	
	var _updateConfig = function () {
		if (window.sessionStorage) {
			window.sessionStorage.setItem("__wsc_devtools_config", JSON.stringify(_settings));
		}
	};
	
	var Devtools = {
		/**
		 * Prints the list of available commands.
		 */
		help: function () {
			window.console.log("");
			window.console.log("%cAvailable commands:", "text-decoration: underline");
			
			var cmds = [];
			for (var cmd in Devtools) {
				if (cmd !== '_internal_' && Devtools.hasOwnProperty(cmd)) {
					cmds.push(cmd);
				}
			}
			cmds.sort().forEach(function(cmd) {
				window.console.log("\tDevtools." + cmd + "()");
			});
			
			window.console.log("");
		},
		
		/**
		 * Disables/re-enables the editor autosave feature.
		 * 
		 * @param       {boolean}       forceDisable
		 */
		toggleEditorAutosave: function(forceDisable) {
			_settings.editorAutosave = (forceDisable === true) ? false : !_settings.editorAutosave;
			_updateConfig();
			
			window.console.log("%c\tEditor autosave " + (_settings.editorAutosave ? "enabled" : "disabled"), "font-style: italic");
		},
		
		/**
		 * Enables/disables logging for fired event listener events.
		 * 
		 * @param       {boolean}       forceEnable
		 */
		toggleEventLogging: function(forceEnable) {
			_settings.eventLogging = (forceEnable === true) ? true : !_settings.eventLogging;
			_updateConfig();
			
			window.console.log("%c\tEvent logging " + (_settings.eventLogging ? "enabled" : "disabled"), "font-style: italic");
		},
		
		/**
		 * Internal methods not meant to be called directly.
		 */
		_internal_: {
			enable: function () {
				window.Devtools = Devtools;
				
				window.console.log("%cDevtools for WoltLab Suite loaded", "font-weight: bold");
				
				if (window.sessionStorage) {
					var settings = window.sessionStorage.getItem("__wsc_devtools_config");
					try {
						if (settings !== null) {
							_settings = JSON.parse(settings);
						}
					}
					catch (e) {}
					
					if (!_settings.editorAutosave) Devtools.toggleEditorAutosave(true);
					if (_settings.eventLogging) Devtools.toggleEventLogging(true);
				}
				
				window.console.log("Settings are saved per browser session, enter `Devtools.help()` to learn more.");
				window.console.log("");
			},
			
			editorAutosave: function () {
				return _settings.editorAutosave;
			},
			
			eventLog: function(identifier, action) {
				if (_settings.eventLogging) {
					window.console.log("[Devtools.EventLogging] Firing event: " + action + " @ " + identifier);
				}
			}
		}
	};
	
	return Devtools;
});
