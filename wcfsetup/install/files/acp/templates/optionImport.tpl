{include file='header' pageTitle='wcf.acp.option.import'}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.option.import{/lang}</h1>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.acp.option.import.success{/lang}</p>
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

<form method="post" action="{link controller='OptionImport'}{/link}" enctype="multipart/form-data">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.acp.option.import{/lang}</legend>
			
			<dl{if $errorField == 'optionImport'} class="formError"{/if}>
				<dt><label for="optionImport">{lang}wcf.acp.option.import.upload{/lang}</label></dt>
				<dd>
					<input type="file" id="optionImport" name="optionImport" value="" />
					{if $errorField == 'optionImport'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.option.import.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.option.import.upload.description{/lang}</small>
				</dd>
			</dl>
			
			{event name='importFields'}
		</fieldset>
		
		{event name='importFieldsets'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" name="submitButton" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.option.export{/lang}</h1>
</header>

<div class="container containerPadding marginTop">
	<fieldset>
		<legend>{lang}wcf.acp.option.export{/lang}</legend>
		
		<dl id="optionExportDiv">
			<dt><label>{lang}wcf.acp.option.export.download{/lang}</label></dt>
			<dd>
				<p><a href="{link controller='OptionExport'}{/link}" id="optionExport" class="button">{lang}wcf.acp.option.export{/lang}</a></p>
				<small>{lang}wcf.acp.option.export.download.description{/lang}</small>
			</dd>
		</dl>
		
		{event name='exportFields'}
	</fieldset>
	
	{event name='exportFieldsets'}
</div>

{include file='footer'}
