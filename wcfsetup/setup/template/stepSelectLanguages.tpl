{include file='header'}

<hgroup class="wcf-subHeading">
	<h1>{lang}wcf.global.languages{/lang}</h1>
	<h2>{lang}wcf.global.languages.description{/lang}</h2>
</hgroup>

{if $errorField}
	<p class="wcf-error">{if $errorType == 'empty'}{lang}wcf.global.languages.error.empty{/lang}{/if}</p>
{/if}

<form method="post" action="install.php">
	<fieldset>
		<legend>{lang}wcf.global.languages.languages{/lang}</legend>
			
		<dl class="languages">
			{foreach from=$availableLanguages key=availableLanguageCode item=languageName}
				<dt></dt>
				<dd><label><input type="checkbox" name="selectedLanguages[]" value="{@$availableLanguageCode}" {if $availableLanguageCode|in_array:$selectedLanguages}checked="checked" {/if}/> {$languageName} ({$availableLanguageCode})</label></dd>
			{/foreach}
		</dl>
	</fieldset>
	
	<div class="wcf-formSubmit">
		<input type="submit" value="{lang}wcf.global.button.next{/lang}" accesskey="s" />
		<input type="hidden" name="send" value="1" />
		<input type="hidden" name="step" value="{@$nextStep}" />
		<input type="hidden" name="tmpFilePrefix" value="{@$tmpFilePrefix}" />
		<input type="hidden" name="languageCode" value="{@$languageCode}" />
		<input type="hidden" name="wcfDir" value="{$wcfDir}" />
		<input type="hidden" name="dev" value="{@$developerMode}" />
	</div>
</form>

{include file='footer'}
