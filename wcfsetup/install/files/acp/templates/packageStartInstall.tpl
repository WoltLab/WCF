{include file='header'}

{if $packageID == 0}
	<div class="mainHeadline">
		<img src="{@RELATIVE_WCF_DIR}icon/packageInstallL.png" alt="" />
		<div class="headlineContainer">
			<h2>{lang}wcf.acp.package.startInstall{/lang}</h2>
		</div>
	</div>
{else}
	<div class="mainHeadline">
		<img src="{@RELATIVE_WCF_DIR}icon/packageUpdateL.png" alt="" />
		<div class="headlineContainer">
			<h2>{lang}wcf.acp.package.startUpdate{/lang}</h2>
		</div>
	</div>
{/if}

{if $errorField != ''}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul>
			<li><a href="index.php?page=PackageList{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.package.view{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/packageM.png" alt="" /> <span>{lang}wcf.acp.menu.link.package.view{/lang}</span></a></li>
			{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
		</ul>
	</div>
</div>
<form method="post" action="index.php?form=PackageStartInstall" enctype="multipart/form-data">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.package.startInstall.source{/lang}</legend>
			
				<div class="formElement{if $errorField == 'uploadPackage'} formError{/if}" id="uploadPackageDiv">
					<div class="formFieldLabel">
						<label for="uploadPackage">{lang}wcf.acp.package.startInstall.source.upload{/lang}</label>
					</div>
					<div class="formField">
						<input type="file" id="uploadPackage" name="uploadPackage" value="" />
						{if $errorField == 'uploadPackage'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'noValidPackage'}{lang}wcf.acp.package.startInstall.error.noValidPackage{/lang}{/if}
								{if $errorType == 'noValidUpdate'}{lang}wcf.acp.package.startInstall.error.noValidUpdate{/lang}{/if}
								{if $errorType == 'noValidInstall'}{lang}wcf.acp.package.startInstall.error.noValidInstall{/lang}{/if}
								{if $errorType == 'uploadFailed'}{lang}wcf.acp.package.startInstall.error.uploadFailed{/lang}{/if}
								{if $errorType == 'uniqueAlreadyInstalled'}{lang}wcf.acp.package.startInstall.error.uniqueAlreadyInstalled{/lang}{/if}
								{if $errorType == 'phpRequirements'}<pre>{$phpRequirements|print_r}</pre>{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="uploadPackageHelpMessage">
						<p>{lang}wcf.acp.package.startInstall.source.upload.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('uploadPackage');
				//]]></script>
				
				<div class="formElement{if $errorField == 'downloadPackage'} formError{/if}" id="downloadPackageDiv">
					<div class="formFieldLabel">
						<label for="downloadPackage">{lang}wcf.acp.package.startInstall.source.download{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="downloadPackage" name="downloadPackage" value="" />
						{if $errorField == 'downloadPackage'}
							<p class="innerError">
								{if $errorType == 'notFound'}{lang}wcf.acp.package.startInstall.error.notFound{/lang}{/if}
								{if $errorType == 'noValidPackage'}{lang}wcf.acp.package.startInstall.error.noValidPackage{/lang}{/if}
								{if $errorType == 'noValidUpdate'}{lang}wcf.acp.package.startInstall.error.noValidUpdate{/lang}{/if}
								{if $errorType == 'noValidInstall'}{lang}wcf.acp.package.startInstall.error.noValidInstall{/lang}{/if}
								{if $errorType == 'uniqueAlreadyInstalled'}{lang}wcf.acp.package.startInstall.error.uniqueAlreadyInstalled{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="downloadPackageHelpMessage">
						<p>{lang}wcf.acp.package.startInstall.source.download.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('downloadPackage');
				//]]></script>
				
			</fieldset>
			
			{if $additionalFields|isset}{@$additionalFields}{/if}
		</div>
	</div>

	<div class="formSubmit">
		<input type="submit" accesskey="s" name="submitButton" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		{@SID_INPUT_TAG}
 		<input type="hidden" name="action" value="{$action}" />
 		{if $packageID != 0}<input type="hidden" name="activePackageID" value="{@$packageID}" />{/if}
	</div>
</form>

{include file='footer'}
