{include file='header'}

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/upload1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.option.import{/lang}</h1>
	</hgroup>
</header>

{if $success|isset}
	<p class="success">{lang}wcf.acp.option.import.success{/lang}</p>	
{/if}

{if $errorField != ''}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<form method="post" action="{link controller='OptionImport'}{/link}" enctype="multipart/form-data">
	<div>
		
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
		</fieldset>
		
		{if $additionalFields|isset}{@$additionalFields}{/if}
	</div>

	<div class="formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" name="submitButton" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
	</div>
</form>

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/download1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.option.export{/lang}</h1>
	</hgroup>
</header>

<div>
	
	<fieldset>
		<legend>{lang}wcf.acp.option.export{/lang}</legend>
	
		<dl id="optionExportDiv">
			<dt><label>{lang}wcf.acp.option.export.download{/lang}</label></dt>
			<dd>
				<p><a href="{link controller='OptionExport'}{/link}" id="optionExport" class="badge badgeButton">{lang}wcf.acp.option.export{/lang}</a></p>
				<small>{lang}wcf.acp.option.export.download.description{/lang}</small>
			</dd>
		</dl>
	</fieldset>
	
	{if $additionalFields|isset}{@$additionalFields}{/if}
</div>

{include file='footer'}
