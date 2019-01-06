{if ENABLE_DEBUG_MODE && ENABLE_DEVELOPER_TOOLS && $__wcf->user->userID}
	<script data-relocate="true">
		require(['Ajax', 'WoltLabSuite/Core/Language/Chooser'], function(Ajax, LanguageChooser) {
			var item = elCreate('li');
			item.id = 'pageLanguageContainer';
			var userPanelItems = elBySel('.userPanelItems');
			userPanelItems.insertBefore(item, userPanelItems.firstChild);
			
			var languages = {
				{implode from=$__wcf->getLanguage()->getLanguages() item=_language}
				'{@$_language->languageID}': {
					iconPath: '{@$_language->getIconPath()|encodeJS}',
					languageName: '{@$_language|encodeJS}',
					languageCode: '{@$_language->languageCode|encodeJS}'
				}
				{/implode}
			};
			
			var callback = function(listItem) {
				Ajax.apiOnce({
					data: {
						actionName: 'devtoolsSetLanguage',
						className: 'wcf\\data\\user\\UserAction',
						parameters: {
							languageID: elData(listItem, 'language-id')
						}
					},
					success: function() {
						window.location.reload();
					}
				});
			};
			
			LanguageChooser.init('pageLanguageContainer', 'pageLanguageID', {@$__wcf->getLanguage()->languageID}, languages, callback);
		});
	</script>
{/if}
