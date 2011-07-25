{include file='header'}

<h2>{lang}wcf.global.languages{/lang}</h2>

<p>{lang}wcf.global.languages.description{/lang}</p>

{if $errorField}
	<p class="error">{if $errorType == 'empty'}{lang}wcf.global.languages.error.empty{/lang}{/if}</p>
{/if}

<form method="post" action="install.php">
	<fieldset>
		<legend>{lang}wcf.global.languages.languages{/lang}</legend>
		
		<div class="inner">
			<ul class="languages">
				{foreach from=$languages key=language item=languageName}
					<li><label><input type="checkbox" name="selectedLanguages[]" value="{@$language}" {if $language|in_array:$selectedLanguages}checked="checked" {/if}/> {@$languageName}</label></li>
				{/foreach}
			</ul>
			<br style="clear: both" />
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
	</div>
</form>

{include file='footer'}