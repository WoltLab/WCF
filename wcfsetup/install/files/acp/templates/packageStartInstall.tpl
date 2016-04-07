{if $package === null}
	{assign var='pageTitle' value='wcf.acp.package.startInstall'}
{else}
	{assign var='pageTitle' value='wcf.acp.package.startUpdate'}
{/if}
{include file='header'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.Language.addObject({
			'wcf.acp.package.install.title': '{lang}wcf.acp.package.install.title{/lang}',
			'wcf.acp.package.update.unauthorized': '{lang}wcf.acp.package.update.unauthorized{/lang}'
		});
		
		WCF.TabMenu.init();
		
		new WCF.ACP.Package.Search();
	});
	//]]>
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}{@$pageTitle}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='PackageList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.package.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if $errorField && $installingImportedStyle}
	<p class="info">{lang}wcf.acp.package.install.installingImportedStyle{/lang}</p>
{/if}

{include file='formError'}

<div class="section tabMenuContainer" data-active="{$activeTabMenuItem}" data-store="activeTabMenuItem">
	<nav class="tabMenu">
		<ul>
			<li><a href="{@$__wcf->getAnchor('packageSearch')}">{lang}wcf.acp.package.search{/lang}</a></li>
			<li><a href="{@$__wcf->getAnchor('upload')}">{lang}wcf.acp.package.upload{/lang}</a></li>
		</ul>
	</nav>
	
	<div id="packageSearch" class="tabMenuContent">
		<div class="section">
			<dl>
				<dt><label for="packageName">{lang}wcf.acp.package.search.packageName{/lang}</label></dt>
				<dd><input type="text" id="packageName" value="" class="long" data-search-name="packageName" /></dd>
			</dl>
			<dl>
				<dt><label for="packageDescription">{lang}wcf.acp.package.search.packageDescription{/lang}</label></dt>
				<dd><input type="text" id="packageDescription" value="" class="long" data-search-name="packageDescription" /></dd>
			</dl>
			<dl>
				<dt><label for="package">{lang}wcf.acp.package.search.package{/lang}</label></dt>
				<dd>
					<input type="text" id="package" value="" class="medium" data-search-name="package" />
					<small>{lang}wcf.acp.package.search.package.description{/lang}</small>
				</dd>
			</dl>
		</div>
		
		<div class="formSubmit">
			<button class="jsButtonPackageSearch">{lang}wcf.global.button.submit{/lang}</button>
		</div>
		
		<section class="section" id="packageSearchResultContainer" style="display: none;">
			<h2 class="sectionTitle">{lang}wcf.acp.package.search.resultList{/lang} <span class="badge">0</span></h2>
			
			<div id="packageSearchResultList"></div>
		</section>
	</div>
	
	<div id="upload" class="tabMenuContent">
		<form method="post" action="{link controller='PackageStartInstall'}{/link}" enctype="multipart/form-data">
			<div class="section">
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
			</div>
			
			<div class="formSubmit">
				<input type="submit" name="submitButton" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
				<input type="hidden" name="action" value="{$action}" />
				{@SECURITY_TOKEN_INPUT_TAG}
			</div>
		</form>
	</div>
</div>

{include file='footer'}
