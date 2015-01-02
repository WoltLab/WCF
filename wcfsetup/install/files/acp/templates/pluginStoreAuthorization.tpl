{if $rejected}
	<p class="error">{lang}wcf.acp.pluginStore.authorization.credentials.rejected{/lang}</p>
{/if}

<fieldset{if $rejected} class="marginTop"{/if}>
	<legend>{lang}wcf.acp.pluginStore.authorization.credentials{/lang}</legend>
	<small>{lang}wcf.acp.pluginStore.authorization.credentials.description{/lang}</small>
	
	<dl>
		<dt><label for="pluginStoreUsername">{lang}wcf.acp.pluginStore.authorization.username{/lang}</label></dt>
		<dd><input type="text" id="pluginStoreUsername" value="" class="long" /></dd>
	</dl>
	
	<dl>
		<dt><label for="pluginStorePassword">{lang}wcf.acp.pluginStore.authorization.password{/lang}</label></dt>
		<dd><input type="password" id="pluginStorePassword" value="" class="long" autocomplete="off" /></dd>
	</dl>
</fieldset>

<div class="formSubmit">
	<button>{lang}wcf.global.button.submit{/lang}</button>
</div>