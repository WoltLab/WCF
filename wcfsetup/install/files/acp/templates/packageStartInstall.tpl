{if $packageID == 0}
	{assign var='pageTitle' value='wcf.acp.package.startInstall'}
{else}
	{assign var='pageTitle' value='wcf.acp.package.startUpdate'}
{/if}
{include file='header'}

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}{@$pageTitle}{/lang}</h1>
	</hgroup>
</header>

{if $errorField != ''}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='PackageList'}{/link}" title="{lang}wcf.acp.menu.link.package.list{/lang}" class="button"><img src="{@$__wcf->getPath()}icon/list.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.menu.link.package.list{/lang}</span></a></li>
			
			{event name='largeButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{link controller='PackageStartInstall'}{/link}" enctype="multipart/form-data">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.acp.package.source{/lang}</legend>
			
			<dl{if $errorField == 'uploadPackage'} class="formError"{/if}>
				<dt><label for="uploadPackage">{lang}wcf.acp.package.source.upload{/lang}</label></dt>
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
								{lang}wcf.acp.package.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.package.source.upload.description{/lang}</small>
				</dd>
			</dl>
			
			<dl{if $errorField == 'downloadPackage'} class="formError"{/if}>
				<dt><label for="downloadPackage">{lang}wcf.acp.package.source.download{/lang}</label></dt>
				<dd>
					<input type="text" id="downloadPackage" name="downloadPackage" value="" class="long" />
					{if $errorField == 'downloadPackage'}
						<small class="innerError">
							{lang}wcf.acp.package.error.{@$errorType}{/lang}
						</small>
					{/if}
					<small>{lang}wcf.acp.package.source.download.description{/lang}</small>
				</dd>
			</dl>
			
			{event name='sourceFields'}
		</fieldset>
		
		{event name='fieldsets'}
	</div>

	<div class="formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" name="submitButton" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
 		<input type="hidden" name="action" value="{$action}" />
 		{if $packageID != 0}<input type="hidden" name="id" value="{@$packageID}" />{/if}
	</div>
</form>

{include file='footer'}
