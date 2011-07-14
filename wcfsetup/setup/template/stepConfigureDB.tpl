{include file='header'}

<h2>{lang}wcf.global.configureDB{/lang}</h2>

<p>{lang}wcf.global.configureDB.description{/lang}</p>

{if $exception|isset}
	<p class="error">{lang}wcf.global.configureDB.error{/lang}</p>
{/if}

{if $conflictedTables|isset}
<p class="error">
	{lang}wcf.global.configureDB.conflictedTables{/lang}
</p>
{/if}

<form method="post" action="install.php">
	<fieldset>
		<legend>{lang}wcf.global.configureDB.accessData{/lang}</legend>
		
		<div class="inner">
			<div>
				<label for="dbClass">{lang}wcf.global.configureDB.class{/lang}</label>
				<select name="dbClass" id="dbClass">
					{foreach from=$availableDBClasses key=dbClassName item=availableDBClass}
						<option value="{@$availableDBClass.class}"{if $availableDBClass.class == $dbClass} selected="selected"{/if}>{lang}wcf.global.configureDB.class.{@$dbClassName}{/lang}</option>
					{/foreach}
				</select>
			</div>

			<div>
				<label for="dbHost">{lang}wcf.global.configureDB.host{/lang}</label>
				<input type="text" class="inputText" id="dbHost" name="dbHost" value="{$dbHost}" style="width: 100%;" />
			</div>
			
			<div>
				<label for="dbUser">{lang}wcf.global.configureDB.user{/lang}</label>
				<input type="text" class="inputText" id="dbUser" name="dbUser" value="{$dbUser}" style="width: 100%;" />
			</div>
			
			<div>
				<label for="dbPassword">{lang}wcf.global.configureDB.password{/lang}</label>
				<input type="password" class="inputText" id="dbPassword" name="dbPassword" value="{$dbPassword}" style="width: 100%;" />
			</div>
			
			<div>
				<label for="dbName">{lang}wcf.global.configureDB.database{/lang}</label>
				<input type="text" class="inputText" id="dbName" name="dbName" value="{$dbName}" style="width: 100%;" />
			</div>
			
			<div>
				<label for="dbNumber">{lang}wcf.global.configureDB.number{/lang}</label>
				<input type="text" class="inputText" id="dbNumber" name="dbNumber" value="{$dbNumber}" style="width: 100%;" />
			</div>
			
			{if $conflictedTables|isset}
			<div>
				<label><input type="checkbox" name="overwriteTables" value="1" /> {lang}wcf.global.configureDB.conflictedTables.overwrite{/lang}</label>
			</div>
			{/if}
		</div>
	</fieldset>
	
	<div class="nextButton">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.next{/lang}" />
		<input type="hidden" name="send" value="1" />
		<input type="hidden" name="step" value="{@$nextStep}" />
		<input type="hidden" name="tmpFilePrefix" value="{@$tmpFilePrefix}" />
		<input type="hidden" name="languageCode" value="{@$languageCode}" />
		<input type="hidden" name="wcfDir" value="{$wcfDir}" />
		<input type="hidden" name="dev" value="{@$developerMode}" />
		{foreach from=$selectedLanguages item=language}
			<input type="hidden" name="selectedLanguages[]" value="{$language}" />
		{/foreach}
	</div>
</form>

{include file='footer'}