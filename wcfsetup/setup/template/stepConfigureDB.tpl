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
		
		<div>
			<div>
				<label for="dbClass">{lang}wcf.global.configureDB.class{/lang}</label>
				<select id="dbClass" name="dbClass">
					{foreach from=$availableDBClasses key=dbClassName item=availableDBClass}
						<option value="{@$availableDBClass.class}"{if $availableDBClass.class == $dbClass} selected="selected"{/if}>{lang}wcf.global.configureDB.class.{@$dbClassName}{/lang}</option>
					{/foreach}
				</select>
			</div>

			<div>
				<label for="dbHost">{lang}wcf.global.configureDB.host{/lang}</label>
				<input type="text" id="dbHost" name="dbHost" value="{$dbHost}" class="long" />
			</div>
			
			<div>
				<label for="dbUser">{lang}wcf.global.configureDB.user{/lang}</label>
				<input type="text" id="dbUser" name="dbUser" value="{$dbUser}" class="long" />
			</div>
			
			<div>
				<label for="dbPassword">{lang}wcf.global.configureDB.password{/lang}</label>
				<input type="password" id="dbPassword" name="dbPassword" value="{$dbPassword}" class="long" />
			</div>
			
			<div>
				<label for="dbName">{lang}wcf.global.configureDB.database{/lang}</label>
				<input type="text" id="dbName" name="dbName" value="{$dbName}" class="long"  />
			</div>
			
			<div>
				<label for="dbNumber">{lang}wcf.global.configureDB.number{/lang}</label>
				<input type="text" id="dbNumber" name="dbNumber" value="{$dbNumber}" class="long" />
			</div>
			
			{if $conflictedTables|isset}
			<div>
				<label><input type="checkbox" name="overwriteTables" value="1" /> {lang}wcf.global.configureDB.conflictedTables.overwrite{/lang}</label>
			</div>
			{/if}
		</div>
	</fieldset>
	
	<div class="nextButton">
		<input type="submit" value="{lang}wcf.global.button.next{/lang}" accesskey="s" />
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
