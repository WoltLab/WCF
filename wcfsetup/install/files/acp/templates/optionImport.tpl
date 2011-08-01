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
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.option.import{/lang}</legend>
			
				<div id="optionImportDiv" class="formElement{if $errorField == 'optionImport'} formError{/if}">
					<div class="formFieldLabel">
						<label for="optionImport">{lang}wcf.acp.option.import.upload{/lang}</label>
					</div>
					<div class="formField">
						<input type="file" id="optionImport" name="optionImport" value="" />
						{if $errorField == 'optionImport'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'importFailed'}{lang}wcf.acp.option.import.error.importFailed{/lang}{/if}
								{if $errorType == 'uploadFailed'}{lang}wcf.acp.option.import.error.uploadFailed{/lang}{/if}
							</p>
						{/if}
					</div>
					<div id="optionImportHelpMessage" class="formFieldDesc hidden">
						<p>{lang}wcf.acp.option.import.upload.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('optionImport');
				//]]></script>
			</fieldset>
			
			{if $additionalFields|isset}{@$additionalFields}{/if}
		</div>
	</div>

	<div class="formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" name="submitButton" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
	</div>
</form>

<div class="border content">
	<div class="container-1">
		<fieldset>
			<legend>{lang}wcf.acp.option.export{/lang}</legend>
		
			<div id="optionExportDiv" class="formElement">
				<div class="formField">
					<a href="index.php?action=OptionExport{@SID_ARG_2ND}" id="optionExport">{lang}wcf.acp.option.export.download{/lang}</a>
				</div>
				<div id="optionExportHelpMessage" class="formFieldDesc hidden">
					<p>{lang}wcf.acp.option.export.download.description{/lang}</p>
				</div>
			</div>
			<script type="text/javascript">//<![CDATA[
				inlineHelp.register('optionExport');
			//]]></script>
		</fieldset>
		
		{if $additionalFields|isset}{@$additionalFields}{/if}
	</div>
</div>

{include file='footer'}
