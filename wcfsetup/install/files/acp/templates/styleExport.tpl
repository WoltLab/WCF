{include file='header' pageTitle='wcf.acp.style.exportStyle'}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.style.exportStyle{/lang}</h1>
</header>

{include file='formError'}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='StyleList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.style.list{/lang}</span></a></li>
			
			{event name='contentNavigationButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{link controller='StyleExport' id=$styleID}{/link}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.acp.style.exportStyle.components{/lang}</legend>
			<small>{lang}wcf.acp.style.exportStyle.components.description{/lang}</small>
			
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" name="exportImages" value="1"{if $exportImages} checked="checked"{/if}{if !$canExportImages} disabled="disabled"{/if} /> <span>{lang}wcf.acp.style.exportImages{/lang}</span></label>
				</dd>
			</dl>
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" name="exportTemplates" value="1"{if $exportTemplates} checked="checked"{/if}{if !$canExportTemplates} disabled="disabled"{/if} /> <span>{lang}wcf.acp.style.exportTemplates{/lang}</span></label>
				</dd>
			</dl>
			
			{event name='componentFields'}
		</fieldset>
		
		{if $style->packageName}
			<fieldset>
				<legend>{lang}wcf.acp.style.exportStyle.asPackage{/lang}</legend>
				<small>{lang}wcf.acp.style.exportStyle.asPackage.description{/lang}</small>
				
				<dl>
					<dt></dt>
					<dd>
						<label><input type="checkbox" id="exportAsPackage" name="exportAsPackage" value="1"{if $exportAsPackage} checked="checked"{/if} /> <span>{lang}wcf.acp.style.exportAsPackage{/lang}</span></label>
					</dd>
				</dl>
				
				{event name='exportAsPackageFields'}
			</fieldset>
		{/if}
		
		{event name='fieldsets'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.acp.style.button.exportStyle{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}