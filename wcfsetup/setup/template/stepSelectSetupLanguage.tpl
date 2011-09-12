{include file='header'}

<hgroup class="subHeading">
	<h1>{lang}wcf.global.welcome{/lang}</h1>
	<h2>{lang}wcf.global.welcome.description{/lang}</h2>
</hgroup>

<form method="post" action="install.php">
	<fieldset>
		<legend>{lang}wcf.global.welcome.language{/lang}</legend>
		
		<label for="languageCode">{lang}wcf.global.welcome.language.description{/lang}</label>
		{htmlOptions name="languageCode" id="languageCode" options=$availableLanguages selected=$languageCode disableEncoding=true}
		<button type="submit" value="{lang}wcf.global.welcome.language.change{/lang}" class="badge badgeButton" />{lang}wcf.global.welcome.language.change{/lang}</button>
		<input type="hidden" name="step" value="selectSetupLanguage" />
		<input type="hidden" name="tmpFilePrefix" value="{@$tmpFilePrefix}" />
	</fieldset>
</form>

<form method="post" action="install.php">
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.next{/lang}" />
		<input type="hidden" name="step" value="{@$nextStep}" />
		<input type="hidden" name="tmpFilePrefix" value="{@$tmpFilePrefix}" />
		<input type="hidden" name="languageCode" value="{@$languageCode}" />
		<input type="hidden" name="dev" value="{@$developerMode}" />
	</div>
</form>

{include file='footer'}
