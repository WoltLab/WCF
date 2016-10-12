{include file='header'}

{if !$errors|empty}
	<p class="error">{lang}wcf.global.applicationDirectory.error{/lang}</p>
{/if}

<form method="post" action="install.php">
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.global.applicationDirectory{/lang}</h2>
			<p class="sectionDescription">{lang}wcf.global.applicationDirectory.description{/lang}</p>
		</header>
		
		{foreach from=$showOrder item=$application}
			<dl{if $errors[$application]|isset} class="formError"{/if}>
				<dt>
					<label for="application_{$application}">{$packages[$application][packageName]}</label>
				</dt>
				<dd>
					<input type="text" id="application_{$application}" class="long jsApplicationDirectory" name="directories[{$application}]" value="{$directories[$application]}">
					{if $errors[$application]|isset}<small class="innerError">{lang}wcf.global.applicationDirectory.error.{@$errors[$application]}{/lang}</small>{/if}
					<small>{$packages[$application][packageDescription]}</small>
				</dd>
			</dl>
		{/foreach}
	</section>
	
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.global.applicationDirectory.url{/lang}</h2>
			<p class="sectionDescription">{lang}wcf.global.applicationDirectory.url.description{/lang}</p>
		</header>
		
		{foreach from=$showOrder item=$application}
			<dl>
				<dt>
					<label for="application_{$application}">{$packages[$application][packageName]}</label>
				</dt>
				<dd>
					<span id="application_{$application}_url"></span>
				</dd>
			</dl>
		{/foreach}
	</section>

	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.next{/lang}" accesskey="s">
		<input type="hidden" name="step" value="{@$nextStep}">
		<input type="hidden" name="tmpFilePrefix" value="{@$tmpFilePrefix}">
		<input type="hidden" name="languageCode" value="{@$languageCode}">
		<input type="hidden" name="dev" value="{@$developerMode}">
	</div>
</form>

<script data-relocate="true">
	(function() {
		function updateUrl(event, directory) {
			directory = (directory) ? directory : event.currentTarget;
			
			var urlElement = document.getElementById(directory.id + '_url');
			var value = directory.value.trim();
			if (value.slice(-1) !== '/') value += '/';
			if (value.substr(0, 1) !== '/') value = '/' + value;
			
			urlElement.textContent = window.location.protocol + '//' + window.location.host + value;
		}
		
		var directory, directories = document.getElementsByClassName('jsApplicationDirectory');
		for (var i = 0, length = directories.length; i < length; i++) {
			directory = directories[i];
			
			updateUrl(undefined, directory);
			
			directory.addEventListener('keyup', updateUrl);
			directory.addEventListener('blur', updateUrl);
		}
	})();
</script>

{include file='footer'}
