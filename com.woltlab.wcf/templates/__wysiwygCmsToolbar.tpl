<script data-relocate="true">
	require(['Language'], function (Language) {
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
	})
</script>

{capture append='__redactorJavaScript'}
	, '{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabArticle.js?v={@LAST_UPDATE_TIME}'
	, '{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabPage.js?v={@LAST_UPDATE_TIME}'
{/capture}
{capture append='__redactorConfig'}
	buttonOptions.woltlabArticle = { icon: 'fa-file-word-o', title: '{jslang}wcf.editor.button.article{/jslang}' };
	buttonOptions.woltlabPage = { icon: 'fa-file-text-o', title: '{jslang}wcf.editor.button.page{/jslang}' };
	
	buttons.push('woltlabPage');
	buttons.push('woltlabArticle');
	
	config.plugins.push('WoltLabArticle');
	config.plugins.push('WoltLabPage');
{/capture}
