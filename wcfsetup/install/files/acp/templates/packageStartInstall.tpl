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
			<li><a href="{link controller='PackageList'}{/link}" title="{lang}wcf.acp.menu.link.package.list{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/package1.svg" alt="" /> <span>{lang}wcf.acp.menu.link.package.list{/lang}</span></a></li>
			{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
		</ul>
	</nav>
</div>

<form method="post" action="{link controller='PackageStartInstall'}{/link}" enctype="multipart/form-data">
	<div class="border content">
		
		<fieldset>
			<legend>{lang}wcf.acp.package.startInstall.source{/lang}</legend>
		
			<dl{if $errorField == 'uploadPackage'} class="formError"{/if}>
				<dt><label for="uploadPackage">{lang}wcf.acp.package.startInstall.source.upload{/lang}</label></dt>
				<dd>
					<input type="file" id="uploadPackage" name="uploadPackage" value="" />
					{if $errorField == 'uploadPackage'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{elseif $errorType == 'phpRequirements'}
								{* todo: use language variable (-> else) *}
								<pre>{$phpRequirements|print_r}</pre>
							{else}
								{lang}wcf.acp.package.startInstall.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.package.startInstall.source.upload.description{/lang}</small>
				</dd>
			</dl>
			
			<dl{if $errorField == 'downloadPackage'} class="formError"{/if}>
				<dt><label for="downloadPackage">{lang}wcf.acp.package.startInstall.source.download{/lang}</label></dt>
				<dd>
					<input type="text" id="downloadPackage" name="downloadPackage" value="" class="long" />
					{if $errorField == 'downloadPackage'}
						<small class="innerError">
							{lang}wcf.acp.package.startInstall.error.{@$errorType}{/lang}
						</small>
					{/if}
					<small>{lang}wcf.acp.package.startInstall.source.download.description{/lang}</small>
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
 		{if $packageID != 0}<input type="hidden" name="id" value="{@$packageID}" />{/if}
	</div>
</form>

{include file='footer'}
