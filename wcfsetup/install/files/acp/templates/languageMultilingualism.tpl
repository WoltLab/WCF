{include file='header' pageTitle='wcf.acp.language.multilingualism'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		var $languageIDs = $('#languageIDs');
		$('#enable').click(function() { $languageIDs.toggle(); });
		{if !$enable}$languageIDs.hide();{/if}
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.language.multilingualism{/lang}</h1>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.edit{/lang}</p>
{/if}

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtons'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

<form enctype="multipart/form-data" method="post" action="{link controller='LanguageMultilingualism'}{/link}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend><label><input type="checkbox" id="enable" name="enable" value="1" {if $enable}checked="checked" {/if}/> {lang}wcf.acp.language.multilingualism.enable{/lang}</label></legend>
			<small>{lang}wcf.acp.language.multilingualism.enable.description{/lang}</small>
			
			<dl id="languageIDs" class="marginTop{if $errorField == 'languageIDs'} formError{/if}">
				<dt><label for="languageIDs">{lang}wcf.acp.language.multilingualism.languages{/lang}</label></dt>
				<dd class="floated">
					{foreach from=$languages item='language'}
						<label><input type="checkbox" name="languageIDs[]" value="{@$language->languageID}"{if $language->languageID == $defaultLanguageID} checked="checked" disabled="disabled"{elseif $language->languageID|in_array:$languageIDs} checked="checked"{/if} /> {$language}</label>
					{/foreach}
					
					{if $errorField == 'languageIDs'}
						<small class="innerError">
							{lang}wcf.acp.language.multilingualism.languages.error.{@$errorType}{/lang}
						</small>
					{/if}
				</dd>
			</dl>
			
			{event name='enableFields'}
		</fieldset>
		
		{event name='fieldsets'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
 	</div>
</form>

{include file='footer'}
