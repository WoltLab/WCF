<template id="boxAddDialog">
	<dl>
		<dt>{lang}wcf.acp.box.type{/lang}</dt>
		<dd>
			<label><input type="radio" name="boxType" value="text" checked> {lang}wcf.acp.box.type.text{/lang}</label>
			<small>{lang}wcf.acp.box.type.text.description{/lang}</small>
			<label><input type="radio" name="boxType" value="html"> {lang}wcf.acp.box.type.html{/lang}</label>
			<small>{lang}wcf.acp.box.type.html.description{/lang}</small>
			<label><input type="radio" name="boxType" value="tpl"> {lang}wcf.acp.box.type.tpl{/lang}</label>
			<small>{lang}wcf.acp.box.type.tpl.description{/lang}</small>
			<label><input type="radio" name="boxType" value="system"> {lang}wcf.acp.box.type.system{/lang}</label>
			<small>{lang}wcf.acp.box.type.system.description{/lang}</small>
		</dd>
	</dl>
	
	{if $availableLanguages|count > 1}
		<dl>
			<dt>{lang}wcf.acp.box.i18n{/lang}</dt>
			<dd>
				<label><input type="radio" name="isMultilingual" value="0" checked> {lang}wcf.acp.box.i18n.none{/lang}</label>
				<small>{lang}wcf.acp.box.i18n.none.description{/lang}</small>
				<label><input type="radio" name="isMultilingual" value="1"> {lang}wcf.acp.box.i18n.i18n{/lang}</label>
				<small>{lang}wcf.acp.box.i18n.i18n.description{/lang}</small>
			</dd>
		</dl>
	{/if}
</template>
<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Acp/Ui/Box/Add'], (Language, { AcpUiBoxAdd }) => {
		Language.addObject({
			'wcf.acp.box.add': '{jslang}wcf.acp.box.add{/jslang}'
		});
		
		const boxAddDialog = new AcpUiBoxAdd(
			'{link controller='BoxAdd' encode=false}{literal}boxType={$boxType}&isMultilingual={$isMultilingual}{/literal}{/link}',
			{if $availableLanguages|count > 1}true{else}false{/if}
		);
		
		{if $showBoxAddDialog}
			window.setTimeout(() => {
				boxAddDialog.show();
			}, 10);
		{/if}
	});
</script>
