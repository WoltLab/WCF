/**
 * Handles installing a project as a package.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Devtools/Project/Installation/Confirmation
 */
define(['Ajax', 'Language', 'Ui/Confirmation'], function(Ajax, Language, UiConfirmation) {
	"use strict";
	
	var _projectId;
	var _projectName;
	
	return {
		/**
		 * Initializes the confirmation to install a project as a package.
		 * 
		 * @param	{int}		projectId	id of the installed project
		 * @param	{string}	projectName	name of the installed project
		 */
		init: function(projectId, projectName) {
			_projectId = projectId;
			_projectName = projectName;
			
			[].forEach.call(elByClass('jsDevtoolsInstallPackage'), function(element) {
				element.addEventListener('click', this._showConfirmation.bind(this));
			}.bind(this));
		},
		
		/**
		 * Starts the package installation.
		 */
		_installPackage: function() {
			Ajax.apiOnce({
				data: {
					actionName: 'installPackage',
					className: 'wcf\\data\\devtools\\project\\DevtoolsProjectAction',
					objectIDs: [ _projectId ]
				},
				success: function(data) {
					var packageInstallation = new WCF.ACP.Package.Installation(
						data.returnValues.queueID,
						'DevtoolsInstallPackage',
						data.returnValues.isApplication,
						false,
						{projectID: _projectId}
					);
					
					packageInstallation.prepareInstallation();
				}
			});
		},
		
		/**
		 * Shows the confirmation to start package installation.
		 */
		_showConfirmation: function() {
			UiConfirmation.show({
				confirm: this._installPackage.bind(this),
				message: Language.get('wcf.acp.devtools.project.installPackage.confirmMessage', {
					packageIdentifier: _projectName
				}),
				messageIsHtml: true
			});
		}
	};
});