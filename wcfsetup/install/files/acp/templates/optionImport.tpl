{include file='header'}

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/optionImportAndExportL.png" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.option.importAndExport{/lang}</h1>
	</hgroup>
</header>

{if $success|isset}
	<p class="success">{lang}wcf.acp.option.import.success{/lang}</p>	
{/if}

{if $errorField != ''}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<form method="post" action="index.php?form=OptionImport" enctype="multipart/form-data">
	<div class="border content">
		
		<fieldset>
			<legend>{lang}wcf.acp.option.import{/lang}</legend>
		
			<dl id="optionImportDiv"{if $errorField == 'optionImport'} class="formError"{/if}>
				<dt><label for="optionImport">{lang}wcf.acp.option.import.upload{/lang}</label></dt>
				<dd>
					<input type="file" id="optionImport" name="optionImport" value="" />
					{if $errorField == 'optionImport'}
						<small class="innerError">
							{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							{if $errorType == 'importFailed'}{lang}wcf.acp.option.import.error.importFailed{/lang}{/if}
							{if $errorType == 'uploadFailed'}{lang}wcf.acp.option.import.error.uploadFailed{/lang}{/if}
						</small>
					{/if}
					<small id="optionImportHelpMessage">{lang}wcf.acp.option.import.upload.description{/lang}</small>
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

<div class="border content">
	
	<fieldset>
		<legend>{lang}wcf.acp.option.export{/lang}</legend>
	
		<dl id="optionExportDiv">
			<dt><label><!-- ToDo: Download label --></label></dt>
			<dd><p><a href="index.php?action=OptionExport{@SID_ARG_2ND}" id="optionExport" class="largeButton">{lang}wcf.acp.option.export.download{/lang}</a></p></dd>
			<small>{lang}wcf.acp.option.export.download.description{/lang}</small>
		</dl>
	</fieldset>
	
	{if $additionalFields|isset}{@$additionalFields}{/if}
</div>

{include file='footer'}
