{include file='header'}

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.style.exportStyle{/lang}</h1>
	</hgroup>
</header>

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='StyleList'}{/link}" title="{lang}wcf.acp.menu.link.style.list{/lang}" class="button"><img src="{@$__wcf->getPath()}icon/list.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.menu.link.style.list{/lang}</span></a></li>
		</ul>
	</nav>
</div>

<form method="post" action="{link controller='StyleExport'}{/link}">
	<div class="container containerPadding marginTop shadow">
		<fieldset>
			<legend>{lang}wcf.acp.style.exportStyle.components{/lang}</legend>
			<small>{lang}wcf.acp.style.exportStyle.components.description{/lang}</small>
			
			<dl>
				<dd>
					<label><input type="checkbox" name="exportIcons" value="1"{if $exportIcons} checked="checked"{/if}{if !$canExportIcons} disabled="disabled"{/if} /> <span>{lang}wcf.acp.style.exportIcons{/lang}</span></label>
				</dd>
			</dl>
			<dl>
				<dd>
					<label><input type="checkbox" name="exportImages" value="1"{if $exportImages} checked="checked"{/if}{if !$canExportImages} disabled="disabled"{/if} /> <span>{lang}wcf.acp.style.exportImages{/lang}</span></label>
				</dd>
			</dl>
			<dl>
				<dd>
					<label><input type="checkbox" name="exportTemplates" value="1"{if $exportTemplates} checked="checked"{/if}{if !$canExportTemplates} disabled="disabled"{/if} /> <span>{lang}wcf.acp.style.exportTemplates{/lang}</span></label>
				</dd>
			</dl>
		</fieldset>
		
		<fieldset>
			<legend>{lang}wcf.acp.style.exportStyle.asPackage{/lang}</legend>
			<small>{lang}wcf.acp.style.exportStyle.asPackage.description{/lang}</small>
			
			<dl>
				<dd>
					<label><input type="checkbox" name="exportAsPackage" value="1"{if $exportAsPackage} checked="checked"{/if} /> <span>{lang}wcf.acp.style.exportAsPackage{/lang}</span></label>
				</dd>
			</dl>
			<dl{if $errorField == 'packageName'} class="formError"{/if}>
				<dt>
					<label for="packageName">{lang}wcf.acp.style.packageName{/lang}</label>
				</dt>
				<dd>
					<input type="text" name="packageName" id="packageName" class="long" value="{$packageName}" />
					{if $errorField == 'packageName'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.style.packageName.error.{$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.style.packageName.description{/lang}</small>
				</dd>
			</dl>
		</fieldset>
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.acp.style.button.exportStyle{/lang}" accesskey="s" />
		<input type="hidden" name="id" value="{@$styleID}" />
	</div>
</form>

{include file='footer'}