{include file='header'}

<form method="post" action="install.php">
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.global.welcome{/lang}</h2>
			<p class="sectionDescription">{lang}wcf.global.welcome.description{/lang}</p>
		</header>
		
		<dl>
			<dt><label for="languageCode">{lang}wcf.global.welcome.language{/lang}</label></dt>
			<dd>
				{capture assign=languageChooser}<select name="languageCode" id="languageCode">
					{foreach from=$availableLanguages key=availableLanguageCode item=languageName}
						<option value="{$availableLanguageCode}"{if $availableLanguageCode == $languageCode} selected{/if}>{$languageName} ({$availableLanguageCode})</option>
					{/foreach}
					</select>{/capture}
				<label for="languageCode">{lang}wcf.global.welcome.language.description{/lang}</label>
			</dd>
		</dl>
		
		<div class="formSubmit">
			<input type="submit" accesskey="s" value="{lang}wcf.global.button.next{/lang}">
			<input type="hidden" name="step" value="{@$nextStep}">
			<input type="hidden" name="tmpFilePrefix" value="{@$tmpFilePrefix}">
			<input type="hidden" name="dev" value="{@$developerMode}">
		</div>
	</section>
</form>

{include file='footer'}
