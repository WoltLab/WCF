{include file='header' pageTitle='wcf.acp.style.exportStyle'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.style.exportStyle{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='StyleList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.style.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

<form method="post" action="{link controller='StyleExport' id=$styleID}{/link}">
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.acp.style.exportStyle.components{/lang}</h2>
			<p class="sectionDescription">{lang}wcf.acp.style.exportStyle.components.description{/lang}</p>
		</header>
		
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" name="exportTemplates" value="1"{if $exportTemplates} checked{/if}{if !$canExportTemplates} disabled{/if}> <span>{lang}wcf.acp.style.exportTemplates{/lang}</span></label>
			</dd>
		</dl>
		
		{event name='componentFields'}
	</section>
	
	{if $style->packageName}
		<section class="section">
			<header class="sectionHeader">
				<h2 class="sectionTitle">{lang}wcf.acp.style.exportStyle.asPackage{/lang}</h2>
				<p class="sectionDescription">{lang}wcf.acp.style.exportStyle.asPackage.description{/lang}</p>
			</header>
			
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" id="exportAsPackage" name="exportAsPackage" value="1"{if $exportAsPackage} checked{/if}> <span>{lang}wcf.acp.style.exportAsPackage{/lang}</span></label>
				</dd>
			</dl>
			
			{event name='exportAsPackageFields'}
		</section>
	{/if}
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.acp.style.button.exportStyle{/lang}" accesskey="s">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
