{if !$wysiwygSelector|isset}{assign var=wysiwygSelector value='text'}{/if}

<script data-relocate="true">
	require(["WoltLabSuite/Core/Component/Article/EditorButton", 'Language'], function ({ setup: setupArticle }, Language) {
		Language.addObject({
			'wcf.article.search': '{jslang}wcf.article.search{/jslang}',
			'wcf.article.search.error.tooShort': '{jslang}wcf.article.search.error.tooShort{/jslang}',
			'wcf.article.search.error.noResults': '{jslang}wcf.article.search.error.noResults{/jslang}',
			'wcf.article.search.name': '{jslang}wcf.article.search.name{/jslang}',
			'wcf.article.search.results': '{jslang}wcf.article.search.results{/jslang}',
			'wcf.page.search': '{jslang}wcf.page.search{/jslang}',
			'wcf.page.search.error.tooShort': '{jslang}wcf.page.search.error.tooShort{/jslang}',
			'wcf.page.search.error.noResults': '{jslang}wcf.page.search.error.noResults{/jslang}',
			'wcf.page.search.name': '{jslang}wcf.page.search.name{/jslang}',
			'wcf.page.search.results': '{jslang}wcf.page.search.results{/jslang}',
		});

		const element = document.getElementById('{$wysiwygSelector|encodeJS}');
		setupArticle(element);
	});
</script>

{capture append='__redactorJavaScript'}
	, '{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabArticle.js?v={@LAST_UPDATE_TIME}'
	, '{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabPage.js?v={@LAST_UPDATE_TIME}'
{/capture}
{capture append='__redactorConfig'}
	buttonOptions.woltlabArticle = { icon: 'file-word;false', title: '{jslang}wcf.editor.button.article{/jslang}' };
	buttonOptions.woltlabPage = { icon: 'file-lines;false', title: '{jslang}wcf.editor.button.page{/jslang}' };
	
	buttons.push('woltlabPage');
	buttons.push('woltlabArticle');
	
	config.plugins.push('WoltLabArticle');
	config.plugins.push('WoltLabPage');
{/capture}
