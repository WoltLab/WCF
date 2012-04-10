{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	{if $enable == 0}
		$(function () {
			$('#languageIDs').hide();
		});
	{/if}
	//]]>
</script>

<header class="box48 boxHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/language1.svg" alt="" class="icon48" />
	<hgroup>
		<h1>{lang}wcf.acp.language.multilingualism{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.language.multilingualism.success{/lang}</p>
{/if}

<form enctype="multipart/form-data" method="post" action="{link controller='LanguageMultilingualism'}{/link}">
	<div class="container containerPadding marginTop shadow">
		<dl>
			<dt><input type="checkbox" id="enable" onclick="if (this.checked) $('#languageIDs').show(); else $('#languageIDs').hide();" name="enable" value="1" {if $enable == 1}checked="checked" {/if}/></dt>
			<dd>
				<label for="enable">{lang}wcf.acp.language.multilingualism.enable{/lang}</label>
				<small>{lang}wcf.acp.language.multilingualism.enable.description{/lang}</small>
			</dd>
		</dl>
		
		<dl{if $errorField == 'languageFile'} class="formError"{/if} id="languageIDs">
			<dt><label for="languageIDs">{lang}wcf.acp.language.multilingualism.languages{/lang}</label></dt>
			<dd>
				<fieldset>
					<legend>{lang}wcf.acp.language.multilingualism.languages{/lang}</legend>
					
					<dd>
						{htmlCheckboxes options=$languages name=languageIDs selected=$languageIDs disableEncoding=true}
					</dd>
				</fieldset>
				
				{if $errorField == 'languageIDs'}
					<small class="innerError">
						{if $errorType == 'empty'}{lang}wcf.acp.language.multilingualism.languages.error.empty{/lang}{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		{event name='additionalFields'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
 	</div>
</form>

{include file='footer'}
