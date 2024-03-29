<template id="pageAddDialog">
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
	
	{if $availableLanguages|count > 1}
		<dl>
			<dt>{lang}wcf.acp.page.i18n{/lang}</dt>
			<dd>
				<label><input type="radio" name="isMultilingual" value="0" checked> {lang}wcf.acp.page.i18n.none{/lang}</label>
				<small>{lang}wcf.acp.page.i18n.none.description{/lang}</small>
				<label><input type="radio" name="isMultilingual" value="1"> {lang}wcf.acp.page.i18n.i18n{/lang}</label>
				<small>{lang}wcf.acp.page.i18n.i18n.description{/lang}</small>
			</dd>
		</dl>
	{/if}
</template>
<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Acp/Ui/Page/Add'], (Language, { AcpUiPageAdd }) => {
		Language.addObject({
			'wcf.acp.page.add': '{jslang}wcf.acp.page.add{/jslang}'
		});
		
		const pageAddDialog = new AcpUiPageAdd(
			'{link controller='PageAdd' encode=false}{literal}pageType={$pageType}&isMultilingual={$isMultilingual}{/literal}{/link}',
			{if $availableLanguages|count > 1}true{else}false{/if}
		);
		
		{if $showPageAddDialog}
			window.setTimeout(() => {
				pageAddDialog.show();
			}, 10);
		{/if}
	});
</script>
