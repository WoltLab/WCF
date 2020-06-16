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
				var languageCode = elData(listItem, 'language-code');
				if (languageCode === '{$__wcf->getLanguage()->getFixedLanguageCode()}') {
					window.location.reload();
					return;
				}
				
				var alternateLink = elBySel('link[rel="alternate"][hreflang="' + languageCode + '"]');
				if (alternateLink) {
					// Check if the page does not have unique links per language, such as for the landing page.
					var currentLink = elBySel('link[rel="alternate"][hreflang="{$__wcf->getLanguage()->getFixedLanguageCode()}"]');
					if (!currentLink || currentLink.href !== alternateLink.href) {
						window.location = alternateLink.href;
						return;
					}
				}
				
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
