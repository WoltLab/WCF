{if ENABLE_DEBUG_MODE && ENABLE_DEVELOPER_TOOLS && $__wcf->user->userID && $__isLogin|empty}
	<script data-relocate="true">
		require(['Ajax', 'WoltLabSuite/Core/Language/Chooser'], (Ajax, LanguageChooser) => {
			const item = elCreate('li');
			item.id = 'pageLanguageContainer';
			
			const userPanelItems = document.querySelector('.userPanelItems');
			userPanelItems.insertBefore(item, userPanelItems.firstChild);
			
			const languages = {
				{implode from=$__wcf->getLanguage()->getLanguages() item=_language}
					'{@$_language->languageID}': {
						iconPath: '{@$_language->getIconPath()|encodeJS}',
						languageName: '{@$_language|encodeJS}',
						languageCode: '{@$_language->languageCode|encodeJS}',
					}
				{/implode}
			};
			
			const callback = (listItem) => {
				Ajax.apiOnce({
					data: {
						actionName: 'devtoolsSetLanguage',
						className: 'wcf\\data\\user\\UserAction',
						parameters: {
							languageID: listItem.dataset.languageId,
						},
					},
					success() {
						window.location.reload();
					},
				});
			};
			
			LanguageChooser.init(
				'pageLanguageContainer',
				'pageLanguageID',
				{@$__wcf->getLanguage()->languageID},
				languages,
				callback
			);
		});
	</script>
{/if}
