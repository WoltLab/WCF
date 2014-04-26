{if $rejected}
	<p class="error">{lang}wcf.acp.pluginstore.authorization.credentails.rejected{/lang}</p>
{/if}

<fieldset{if $rejected} class="marginTop"{/if}>
	<legend>{lang}wcf.acp.pluginstore.authorization.credentials{/lang}</legend>
	<small>{lang}wcf.acp.pluginstore.authorization.credentails.description{/lang}</small>
	
	<dl>
		<dt><label for="pluginStoreUsername">{lang}wcf.acp.pluginstore.authorization.username{/lang}</label></dt>
		<dd><input type="text" id="pluginStoreUsername" value="" class="long" /></dd>
	</dl>
	
	<dl>
		<dt><label for="pluginStorePassword">{lang}wcf.acp.pluginstore.authorization.password{/lang}</label></dt>
		<dd><input type="password" id="pluginStorePassword" value="" class="long" /></dd>
	</dl>
</fieldset>

<div class="formSubmit">
	<button>{lang}wcf.global.button.submit{/lang}</button>
</div>