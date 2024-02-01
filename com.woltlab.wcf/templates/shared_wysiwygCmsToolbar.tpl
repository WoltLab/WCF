{if !$wysiwygSelector|isset}{assign var=wysiwygSelector value='text'}{/if}

<script data-relocate="true">
	require([
		"WoltLabSuite/Core/Component/Article/EditorButton",
		"WoltLabSuite/Core/Component/Page/EditorButton"
	], (
		{ setup: setupArticle },
		{ setup: setupPage }
	) => {
		{jsphrase name='wcf.article.search'}
		{jsphrase name='wcf.article.search.error.tooShort'}
		{jsphrase name='wcf.article.search.error.noResults'}
		{jsphrase name='wcf.article.search.name'}
		{jsphrase name='wcf.article.search.results'}
		{jsphrase name='wcf.editor.button.article'}
		{jsphrase name='wcf.editor.button.page'}
		{jsphrase name='wcf.page.search'}
		{jsphrase name='wcf.page.search.error.tooShort'}
		{jsphrase name='wcf.page.search.error.noResults'}
		{jsphrase name='wcf.page.search.name'}
		{jsphrase name='wcf.page.search.results'}

		const element = document.getElementById('{$wysiwygSelector|encodeJS}');
		setupArticle(element);
		setupPage(element);
	});
</script>
