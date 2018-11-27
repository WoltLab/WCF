<script data-relocate="true">
	require(['Language'], function (Language) {
		Language.addObject({
			'wcf.article.search': '{lang}wcf.article.search{/lang}',
			'wcf.article.search.error.tooShort': '{lang}wcf.article.search.error.tooShort{/lang}',
			'wcf.article.search.error.noResults': '{lang}wcf.article.search.error.noResults{/lang}',
			'wcf.article.search.name': '{lang}wcf.article.search.name{/lang}',
			'wcf.article.search.results': '{lang}wcf.article.search.results{/lang}',
			'wcf.page.search': '{lang}wcf.page.search{/lang}',
			'wcf.page.search.error.tooShort': '{lang}wcf.page.search.error.tooShort{/lang}',
			'wcf.page.search.error.noResults': '{lang}wcf.page.search.error.noResults{/lang}',
			'wcf.page.search.name': '{lang}wcf.page.search.name{/lang}',
			'wcf.page.search.results': '{lang}wcf.page.search.results{/lang}',
		});
	})
</script>

{capture append='__redactorJavaScript'}
	, '{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabArticle.js?v={@LAST_UPDATE_TIME}'
	, '{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabPage.js?v={@LAST_UPDATE_TIME}'
{/capture}
{capture append='__redactorConfig'}
	buttonOptions.woltlabArticle = { icon: 'fa-file-word-o', title: '{lang}wcf.editor.button.article{/lang}' };
	buttonOptions.woltlabPage = { icon: 'fa-file-text-o', title: '{lang}wcf.editor.button.page{/lang}' };
	
	buttons.push('woltlabPage');
	buttons.push('woltlabArticle');
	
	config.plugins.push('WoltLabArticle');
	config.plugins.push('WoltLabPage');
{/capture}
