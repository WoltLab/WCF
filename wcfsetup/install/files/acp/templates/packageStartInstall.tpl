{include file='header'}

<header class="mainHeading">
	{if $packageID == 0}
		<img src="{@RELATIVE_WCF_DIR}icon/add1.svg" alt="" />
		<hgroup>
			<h1>{lang}wcf.acp.package.startInstall{/lang}</h1>
		</hgroup>
	{else}
		<img src="{@RELATIVE_WCF_DIR}icon/update1.svg" alt="" />
		<hgroup>
			<h1>{lang}wcf.acp.package.startUpdate{/lang}</h1>
		</hgroup>
	{/if}
</header>

{if $errorField != ''}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<div class="contentHeader">
	<nav class="largeButtons">
		<ul>
			<li><a href="index.php?page=PackageList{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.package.list{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/package1.svg" alt="" /> <span>{lang}wcf.acp.menu.link.package.list{/lang}</span></a></li>
			{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
		</ul>
	</nav>
</div>

<form method="post" action="index.php?form=PackageStartInstall" enctype="multipart/form-data">
	<div class="border content">
		
		<fieldset>
			<legend>{lang}wcf.acp.package.startInstall.source{/lang}</legend>
		
			<dl id="uploadPackageDiv"{if $errorField == 'uploadPackage'} class="formError"{/if}>
				<dt><label for="uploadPackage">{lang}wcf.acp.package.startInstall.source.upload{/lang}</label></dt>
				<dd>
					<input type="file" id="uploadPackage" name="uploadPackage" value="" />
					{if $errorField == 'uploadPackage'}
						<small class="innerError">
							<span class="arrowOuter" style="display: none;"><span class="arrowInner"></span></span>
							{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
							{if $errorType == 'noValidPackage'}{lang}wcf.acp.package.startInstall.error.noValidPackage{/lang}{/if}
							{if $errorType == 'noValidUpdate'}{lang}wcf.acp.package.startInstall.error.noValidUpdate{/lang}{/if}
							{if $errorType == 'noValidInstall'}{lang}wcf.acp.package.startInstall.error.noValidInstall{/lang}{/if}
							{if $errorType == 'uploadFailed'}{lang}wcf.acp.package.startInstall.error.uploadFailed{/lang}{/if}
							{if $errorType == 'uniqueAlreadyInstalled'}{lang}wcf.acp.package.startInstall.error.uniqueAlreadyInstalled{/lang}{/if}
							{if $errorType == 'phpRequirements'}<pre>{$phpRequirements|print_r}</pre>{/if}
						</small>
					{/if}
					<small id="uploadPackageHelpMessage">{lang}wcf.acp.package.startInstall.source.upload.description{/lang}</small>
				</dd>
			</dl>
			
			<dl id="downloadPackageDiv"{if $errorField == 'downloadPackage'} class="formError"{/if}>
				<dt><label for="downloadPackage">{lang}wcf.acp.package.startInstall.source.download{/lang}</label></dt>
				<dd>
					<input type="text" id="downloadPackage" name="downloadPackage" value="" class="long" />
					{if $errorField == 'downloadPackage'}
						<small class="innerError">
							<span class="arrowOuter" style="display: none;"><span class="arrowInner"></span></span>
							{if $errorType == 'notFound'}{lang}wcf.acp.package.startInstall.error.notFound{/lang}{/if}
							{if $errorType == 'noValidPackage'}{lang}wcf.acp.package.startInstall.error.noValidPackage{/lang}{/if}
							{if $errorType == 'noValidUpdate'}{lang}wcf.acp.package.startInstall.error.noValidUpdate{/lang}{/if}
							{if $errorType == 'noValidInstall'}{lang}wcf.acp.package.startInstall.error.noValidInstall{/lang}{/if}
							{if $errorType == 'uniqueAlreadyInstalled'}{lang}wcf.acp.package.startInstall.error.uniqueAlreadyInstalled{/lang}{/if}
						</small>
					{/if}
					<small id="downloadPackageHelpMessage">{lang}wcf.acp.package.startInstall.source.download.description{/lang}</small>
				</dd>
			</dl>
		</fieldset>
		
		{if $additionalFields|isset}{@$additionalFields}{/if}
	</div>

	<div class="formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" name="submitButton" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
 		<input type="hidden" name="action" value="{$action}" />
 		{if $packageID != 0}<input type="hidden" name="packageID" value="{@$packageID}" />{/if}
	</div>
</form>

{include file='footer'}
