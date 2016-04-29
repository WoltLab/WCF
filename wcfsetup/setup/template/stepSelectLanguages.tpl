{include file='header'}

{if $errorField}
	<p class="error">{if $errorType == 'empty'}{lang}wcf.global.languages.error.empty{/lang}{/if}</p>
{/if}

<form method="post" action="install.php">
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.global.languages{/lang}</h2>
			<p class="sectionDescription">{lang}wcf.global.languages.description{/lang}</p>
		</header>
	
		<dl>
			<dt>{lang}wcf.global.languages.languages{/lang}</dt>
			<dd>
				{foreach from=$availableLanguages key=availableLanguageCode item=languageName}
					<label><input type="checkbox" name="selectedLanguages[]" value="{@$availableLanguageCode}" {if $availableLanguageCode|in_array:$selectedLanguages}checked="checked" {/if}/> {$languageName} ({$availableLanguageCode})</label>
				{/foreach}
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
	</div>
</form>

{include file='footer'}
