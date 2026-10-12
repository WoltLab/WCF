{if ENABLE_DEBUG_MODE && ENABLE_DEVELOPER_TOOLS && !$__wcf->user->isGuest() && $__isLogin|empty}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Api/Devtools/SetLanguage', 'WoltLabSuite/Core/Language/Chooser'], function(SetLanguage, LanguageChooser) {
			var item = elCreate('li');
			item.id = 'pageLanguageContainer';
			var userPanelItems = elBySel('.userPanelItems');
			userPanelItems.insertBefore(item, userPanelItems.firstChild);
			
			var languages = {
				{implode from=$__wcf->getLanguage()->getLanguages() item=_language}
				'{$_language->languageID}': {
					iconPath: '{unsafe:$_language->getIconPath()|encodeJS}',
					languageName: '{unsafe:$_language|encodeJS}',
					languageCode: '{unsafe:$_language->languageCode|encodeJS}'
				}
				{/implode}
			};
			
			var callback = function(listItem) {
				SetLanguage.setLanguage(parseInt(elData(listItem, 'language-id'), 10)).then(function() {
					window.location.reload();
				});
			};
			
			LanguageChooser.init('pageLanguageContainer', 'pageLanguageID', {$__wcf->getLanguage()->languageID}, languages, callback);
		});
	</script>
{/if}
