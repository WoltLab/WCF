<script>
	require(['Language', 'WoltLabSuite/Core/Ui/Style/FontAwesome'], function (Language, UiStyleFontAwesome) {
		Language.addObject({
			'wcf.global.filter.button.clear': '{jslang}wcf.global.filter.button.clear{/jslang}',
			'wcf.global.filter.error.noMatches': '{jslang}wcf.global.filter.error.noMatches{/jslang}',
			'wcf.global.filter.placeholder': '{jslang}wcf.global.filter.placeholder{/jslang}',
			'wcf.global.fontAwesome.selectIcon': '{jslang}wcf.global.fontAwesome.selectIcon{/jslang}'
		});
		
		UiStyleFontAwesome.setup({@$__wcf->getStyleHandler()->getIcons(true)});
	});
</script>
