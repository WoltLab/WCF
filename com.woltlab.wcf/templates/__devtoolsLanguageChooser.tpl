{* The `system_pageHeader` renders this chooser on the server, the new page footers host it instead. *}
{if ENABLE_DEBUG_MODE && ENABLE_DEVELOPER_TOOLS && !$__wcf->user->isGuest() && $__wcf->getStyleHandler()->getStyle()->usesClassicPageHeader() && $__wcf->getStyleHandler()->getStyle()->usesClassicPageFooter()}
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
				var languageCode = elData(listItem, 'language-code');
				if (languageCode === '{$__wcf->getLanguage()->getFixedLanguageCode()}') {
					window.location.reload();
					return;
				}
				
				var alternateLink = elBySel('link[rel="alternate"][hreflang="' + languageCode + '"]');
				if (
					alternateLink
					&& document.body.dataset.application === "wcf"
					&& document.body.dataset.template === "cms"
				) {
					// Check if the page does not have unique links per language, such as for the landing page.
					var currentLink = elBySel('link[rel="alternate"][hreflang="{$__wcf->getLanguage()->getFixedLanguageCode()}"]');
					if (!currentLink || currentLink.href !== alternateLink.href) {
						window.location = alternateLink.href;
						return;
					}
				}
				
				SetLanguage.setLanguage(parseInt(elData(listItem, 'language-id'), 10)).then(function() {
					window.location.reload();
				});
			};
			
			LanguageChooser.init('pageLanguageContainer', 'pageLanguageID', {$__wcf->getLanguage()->languageID}, languages, callback);
		});
	</script>
{/if}
