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
			<dt><label for="dbClass">{lang}wcf.global.configureDB.class{/lang}</label></dt>
			<dd>
				<select id="dbClass" name="dbClass">
					{foreach from=$availableDBClasses key=dbClassName item=availableDBClass}
						<option value="{@$availableDBClass.class}"{if $availableDBClass.class == $dbClass} selected="selected"{/if}>{lang}wcf.global.configureDB.class.{@$dbClassName}{/lang}</option>
					{/foreach}
				</select>
			</dd>
		</dl>
		
		<dl>
			<dt><label for="dbHost">{lang}wcf.global.configureDB.host{/lang}</label></dt>
			<dd><input type="text" id="dbHost" name="dbHost" value="{$dbHost}" required="required" class="long" /></dd>
		</dl>
		
		<dl>
			<dt><label for="dbUser">{lang}wcf.global.configureDB.user{/lang}</label></dt>
			<dd><input type="text" id="dbUser" name="dbUser" value="{$dbUser}" required="required" class="medium" /></dd>
		</dl>
		
		<dl>
			<dt><label for="dbPassword">{lang}wcf.global.configureDB.password{/lang}</label></dt>
			<dd><input type="password" id="dbPassword" name="dbPassword" value="{$dbPassword}" class="medium" /></dd>
		</dl>
		
		<dl>
			<dt><label for="dbName">{lang}wcf.global.configureDB.database{/lang}</label></dt>
			<dd>
				<input type="text" id="dbName" name="dbName" value="{$dbName}" required="required" class="medium" />
				<small>{lang}wcf.global.configureDB.database.description{/lang}</small>
			</dd>
		</dl>
		
		<dl>
			<dt><label for="dbNumber">{lang}wcf.global.configureDB.number{/lang}</label></dt>
			<dd>
				<input type="number" id="dbNumber" name="dbNumber" value="{$dbNumber}" required="required" min="1" class="short" />
				<small>{lang}wcf.global.configureDB.number.description{/lang}</small>
			</dd>
		</dl>
	</section>
		
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.next{/lang}" accesskey="s" />
		<input type="hidden" name="send" value="1" />
		<input type="hidden" name="step" value="{@$nextStep}" />
		<input type="hidden" name="tmpFilePrefix" value="{@$tmpFilePrefix}" />
		<input type="hidden" name="languageCode" value="{@$languageCode}" />
		<input type="hidden" name="dev" value="{@$developerMode}" />
		{foreach from=$directories key=application item=directory}
			<input type="hidden" name="directories[{$application}]" value="{$directory}">
		{/foreach}
		{foreach from=$selectedLanguages item=language}
			<input type="hidden" name="selectedLanguages[]" value="{$language}" />
		{/foreach}
	</div>
</form>

{include file='footer'}
