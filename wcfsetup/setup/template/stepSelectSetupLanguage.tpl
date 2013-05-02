{include file='header'}

<header class="boxHeadline boxSubHeadline">
	<h2>{lang}wcf.global.welcome{/lang}</h2>
	<p>{lang}wcf.global.welcome.description{/lang}</p>
</header>

<form method="post" action="install.php">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.global.welcome.language{/lang}</legend>
			
			{capture assign=languageChooser}<select name="languageCode" id="languageCode">
				{foreach from=$availableLanguages key=availableLanguageCode item=languageName}
					<option value="{$availableLanguageCode}"{if $availableLanguageCode == $languageCode} selected="selected"{/if}>{$languageName} ({$availableLanguageCode})</option>
				{/foreach}
			</select>{/capture}
			<label for="languageCode">{lang}wcf.global.welcome.language.description{/lang}</label>
		</fieldset>
	</div>
	
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.next{/lang}" />
		<input type="hidden" name="step" value="{@$nextStep}" />
		<input type="hidden" name="tmpFilePrefix" value="{@$tmpFilePrefix}" />
		<input type="hidden" name="dev" value="{@$developerMode}" />
	</div>
</form>

{include file='footer'}
