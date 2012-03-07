{include file='header'}

<header class="wcf-container wcf-mainHeading">
	{if $packageID == 0}
		<img src="{@$__wcf->getPath()}icon/add1.svg" alt="" class="wcf-containerIcon" />
		<hgroup class="wcf-containerContent">
			<h1>{lang}wcf.acp.package.startInstall{/lang}</h1>
		</hgroup>
	{else}
		<img src="{@$__wcf->getPath()}icon/update1.svg" alt="" class="wcf-containerIcon" />
		<hgroup class="wcf-containerContent">
			<h1>{lang}wcf.acp.package.startUpdate{/lang}</h1>
		</hgroup>
	{/if}
</header>

{if $errorField != ''}
	<p class="wcf-error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<div class="wcf-contentHeader">
	<nav>
		<ul class="wcf-largeButtons">
			<li><a href="{link controller='PackageList'}{/link}" title="{lang}wcf.acp.menu.link.package.list{/lang}" class="wcf-button"><img src="{@$__wcf->getPath()}icon/packageApplication1.svg" alt="" /> <span>{lang}wcf.acp.menu.link.package.list{/lang}</span></a></li>
			
			{event name='largeButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{link controller='PackageStartInstall'}{/link}" enctype="multipart/form-data">
	<div class="wcf-box wcf-boxDecor wcf-marginTop wcf-boxPadding wcf-shadow1">
		
		<fieldset>
			<legend>{lang}wcf.acp.package.source{/lang}</legend>
		
			<dl{if $errorField == 'uploadPackage'} class="wcf-formError"{/if}>
				<dt><label for="uploadPackage">{lang}wcf.acp.package.source.upload{/lang}</label></dt>
				<dd>
					<input type="file" id="uploadPackage" name="uploadPackage" value="" />
					{if $errorField == 'uploadPackage'}
						<small class="wcf-innerError">
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
			
			<dl{if $errorField == 'downloadPackage'} class="wcf-formError"{/if}>
				<dt><label for="downloadPackage">{lang}wcf.acp.package.source.download{/lang}</label></dt>
				<dd>
					<input type="text" id="downloadPackage" name="downloadPackage" value="" class="long" />
					{if $errorField == 'downloadPackage'}
						<small class="wcf-innerError">
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

	<div class="wcf-formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" name="submitButton" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
 		<input type="hidden" name="action" value="{$action}" />
 		{if $packageID != 0}<input type="hidden" name="id" value="{@$packageID}" />{/if}
	</div>
</form>

{include file='footer'}
