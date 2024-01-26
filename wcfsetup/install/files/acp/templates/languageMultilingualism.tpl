{include file='header' pageTitle='wcf.acp.language.multilingualism'}

<script data-relocate="true">
	$(function() {
		var $languageIDs = $('#languageIDs');
		$('#enable').click(function() { $languageIDs.toggle(); });
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.language.multilingualism{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{include file='shared_formError'}

{if $success|isset}
	<woltlab-core-notice type="success">{lang}wcf.global.success.edit{/lang}</woltlab-core-notice>
{/if}

<form enctype="multipart/form-data" method="post" action="{link controller='LanguageMultilingualism'}{/link}">
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle"><label><input type="checkbox" id="enable" name="enable" value="1"{if $enable} checked{/if}> {lang}wcf.acp.language.multilingualism.enable{/lang}</label></h2>
			<p class="sectionDescription">{lang}wcf.acp.language.multilingualism.enable.description{/lang}</p>
		</header>
		
		<dl id="languageIDs"{if $errorField == 'languageIDs'} class="formError"{/if}{if !$enable} style="display: none;"{/if}>
			<dt><label for="languageIDs">{lang}wcf.acp.language.multilingualism.languages{/lang}</label></dt>
			<dd class="floated">
				{foreach from=$languages item='language'}
					<label><input type="checkbox" name="languageIDs[]" value="{$language->languageID}"{if $language->languageID == $defaultLanguageID} checked disabled{elseif $language->languageID|in_array:$languageIDs} checked{/if}> {$language}</label>
				{/foreach}
				
				{if $errorField == 'languageIDs'}
					<small class="innerError">
						{lang}wcf.acp.language.multilingualism.languages.error.{@$errorType}{/lang}
					</small>
				{/if}
			</dd>
		</dl>
		
		{event name='enableFields'}
	</section>
	
	{event name='sections'}
		
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}">
		{csrfToken}
	</div>
</form>

{include file='footer'}
