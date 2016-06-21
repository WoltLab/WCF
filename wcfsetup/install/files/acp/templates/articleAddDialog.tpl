<div id="articleAddDialog" style="display: none">
	<div class="section">
		<dl>
			<dt>{lang}wcf.acp.article.i18n{/lang}</dt>
			<dd>
				<label><input type="radio" name="isMultilingual" value="0" checked> {lang}wcf.acp.article.i18n.none{/lang}</label>
				<small>{lang}wcf.acp.article.i18n.none.description{/lang}</small>
				<label><input type="radio" name="isMultilingual" value="1"> {lang}wcf.acp.article.i18n.i18n{/lang}</label>
				<small>{lang}wcf.acp.article.i18n.i18n.description{/lang}</small>
			</dd>
		</dl>
		
		<div class="formSubmit">
			<button class="buttonPrimary">{lang}wcf.global.button.next{/lang}</button>
		</div>
	</div>
</div>
<script data-relocate="true">
	require(['Language', 'WoltLab/WCF/Acp/Ui/Article/Add'], function(Language, AcpUiArticleAdd) {
		Language.addObject({
			'wcf.acp.article.add': '{lang}wcf.acp.article.add{/lang}'
		});
		
		AcpUiArticleAdd.init('{link controller='ArticleAdd' encode=false}{literal}isMultilingual={$isMultilingual}{/literal}{if $categoryID}&categoryID={@$categoryID}{/if}{/link}');
		
		{if $showArticleAddDialog}
			window.setTimeout(function() {
				AcpUiArticleAdd.openDialog();
			}, 10);
		{/if}
	});
</script>
