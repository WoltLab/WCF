{if $rejected}
	<p class="error">{lang}wcf.acp.pluginStore.authorization.credentials.rejected{/lang}</p>
{/if}

<section class="section">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.acp.pluginStore.authorization.credentials{/lang}</h2>
		<small class="sectionDescription">{lang}wcf.acp.pluginStore.authorization.credentials.description{/lang}</small>
	</header>
	
	<dl>
		<dt><label for="pluginStoreUsername">{lang}wcf.acp.pluginStore.authorization.username{/lang}</label></dt>
		<dd><input type="text" id="pluginStoreUsername" value="" class="long"></dd>
	</dl>
	
	<dl>
		<dt><label for="pluginStorePassword">{lang}wcf.acp.pluginStore.authorization.password{/lang}</label></dt>
		<dd><input type="password" id="pluginStorePassword" value="" class="long" autocomplete="off"></dd>
	</dl>
</section>

<div class="formSubmit">
	<button>{lang}wcf.global.button.submit{/lang}</button>
</div>