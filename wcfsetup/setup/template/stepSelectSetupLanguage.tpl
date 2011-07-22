{include file='header'}

<h2>{lang}wcf.global.welcome{/lang}</h2>

<p>{lang}wcf.global.welcome.description{/lang}</p>

<form method="post" action="install.php">
	<fieldset>
		<legend>{lang}wcf.global.welcome.language{/lang}</legend>
		
		<div class="inner">
			<div>
				<label for="languageCode">{lang}wcf.global.welcome.language.description{/lang}</label>
				{htmlOptions name="languageCode" id="languageCode" options=$availableLanguages selected=$languageCode disableEncoding=true}
				
				<input type="submit" value="{lang}wcf.global.welcome.language.change{/lang}" />
				<input type="hidden" name="step" value="selectSetupLanguage" />
				<input type="hidden" name="tmpFilePrefix" value="{@$tmpFilePrefix}" />
			</div>
		
		</div>
		
	</fieldset>
</form>

<form method="post" action="install.php">
	<div class="nextButton">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.next{/lang}" />
		<input type="hidden" name="step" value="{@$nextStep}" />
		<input type="hidden" name="tmpFilePrefix" value="{@$tmpFilePrefix}" />
		<input type="hidden" name="languageCode" value="{@$languageCode}" />
		<input type="hidden" name="dev" value="{@$developerMode}" />
	</div>
</form>

{include file='footer'}