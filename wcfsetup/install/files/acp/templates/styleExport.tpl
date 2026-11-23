{include file='header' pageTitle='wcf.acp.style.exportStyle'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.style.exportStyle{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{include file='shared_formError'}

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
		{csrfToken}
	</div>
</form>

{include file='footer'}
