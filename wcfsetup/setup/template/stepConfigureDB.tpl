{include file='header'}

{if $exception|isset}
	<p class="error">{lang}wcf.global.configureDB.error{/lang}</p>
{/if}

{if $conflictedTables|isset}
	<p class="error">{lang}wcf.global.configureDB.conflictedTables{/lang}</p>
{/if}

<form method="post" action="install.php">
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.global.configureDB{/lang}</h2>
			<p class="sectionDescription">{lang}wcf.global.configureDB.description{/lang}</p>
		</header>
	
		<dl>
			<dt><label for="dbHost">{lang}wcf.global.configureDB.host{/lang}</label></dt>
			<dd><input type="text" id="dbHost" name="dbHost" value="{$dbHost}" required class="long"></dd>
		</dl>
		
		<dl>
			<dt><label for="dbUser">{lang}wcf.global.configureDB.user{/lang}</label></dt>
			<dd><input type="text" id="dbUser" name="dbUser" value="{$dbUser}" required class="medium"></dd>
		</dl>
		
		<dl>
			<dt><label for="dbPassword">{lang}wcf.global.configureDB.password{/lang}</label></dt>
			<dd><input type="password" id="dbPassword" name="dbPassword" value="{$dbPassword}" class="medium"></dd>
		</dl>
		
		<dl>
			<dt><label for="dbName">{lang}wcf.global.configureDB.database{/lang}</label></dt>
			<dd>
				<input type="text" id="dbName" name="dbName" value="{$dbName}" required class="medium">
				<small>{lang}wcf.global.configureDB.database.description{/lang}</small>
			</dd>
		</dl>
	</section>
		
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.next{/lang}" accesskey="s">
		<input type="hidden" name="send" value="1">
		<input type="hidden" name="step" value="{$nextStep}">
		<input type="hidden" name="tmpFilePrefix" value="{$tmpFilePrefix}">
		<input type="hidden" name="languageCode" value="{$languageCode}">
		<input type="hidden" name="dev" value="{$developerMode}">
	</div>
</form>
<script>
if (typeof window._trackWcfSetupStep === 'function') window._trackWcfSetupStep('configureDB');
</script>
{include file='footer'}
