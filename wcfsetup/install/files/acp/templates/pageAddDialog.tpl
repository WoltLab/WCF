<div id="pageAddDialog" style="display: none">
	<div class="section">
		<dl>
			<dt>{lang}wcf.acp.page.type{/lang}</dt>
			<dd>
				<label><input type="radio" name="pageType" value="text" checked> {lang}wcf.acp.page.type.text{/lang}</label>
				<small>{lang}wcf.acp.page.type.text.description{/lang}</small>
				<label><input type="radio" name="pageType" value="html"> {lang}wcf.acp.page.type.html{/lang}</label>
				<small>{lang}wcf.acp.page.type.html.description{/lang}</small>
				<label><input type="radio" name="pageType" value="tpl"> {lang}wcf.acp.page.type.tpl{/lang}</label>
				<small>{lang}wcf.acp.page.type.tpl.description{/lang}</small>
			</dd>
		</dl>
		
		<dl>
			<dt>{lang}wcf.acp.page.i18n{/lang}</dt>
			<dd>
				<label><input type="radio" name="isMultilingual" value="0" checked> {lang}wcf.acp.page.i18n.none{/lang}</label>
				<small>{lang}wcf.acp.page.i18n.none.description{/lang}</small>
				<label><input type="radio" name="isMultilingual" value="1"> {lang}wcf.acp.page.i18n.i18n{/lang}</label>
				<small>{lang}wcf.acp.page.i18n.i18n.description{/lang}</small>
			</dd>
		</dl>
		
		<div class="formSubmit">
			<button class="buttonPrimary">{lang}wcf.global.button.next{/lang}</button>
		</div>
	</div>
</div>
<script data-relocate="true">
	require(['Language', 'WoltLab/WCF/Acp/Ui/Page/Add'], function(Language, AcpUiPageAdd) {
		Language.addObject({
			'wcf.acp.page.add': '{lang}wcf.acp.page.add{/lang}'
		});
		
		AcpUiPageAdd.init('{link controller='PageAdd' encode=false}{literal}pageType={$pageType}&isMultilingual={$isMultilingual}{/literal}{/link}');
		
		{if $showPageAddDialog}
			window.setTimeout(function() {
				AcpUiPageAdd.openDialog();
			}, 10);
		{/if}
	});
</script>
