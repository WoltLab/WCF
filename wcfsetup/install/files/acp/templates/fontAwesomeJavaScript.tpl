<script>
	require(['Language', 'WoltLabSuite/Core/Ui/Style/FontAwesome'], function (Language, UiStyleFontAwesome) {
		Language.addObject({
			'wcf.global.filter.button.clear': '{lang}wcf.global.filter.button.clear{/lang}',
			'wcf.global.filter.error.noMatches': '{lang}wcf.global.filter.error.noMatches{/lang}',
			'wcf.global.filter.placeholder': '{lang}wcf.global.filter.placeholder{/lang}',
			'wcf.global.fontAwesome.selectIcon': '{lang}wcf.global.fontAwesome.selectIcon{/lang}'
		});
		
		UiStyleFontAwesome.setup({@$__wcf->getStyleHandler()->getIcons(true)});
	});
</script>
