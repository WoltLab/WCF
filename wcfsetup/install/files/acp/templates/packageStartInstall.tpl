{if $package === null}
	{assign var='pageTitle' value='wcf.acp.package.startInstall'}
{else}
	{assign var='pageTitle' value='wcf.acp.package.startUpdate'}
{/if}
{include file='header'}

<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Acp/Ui/Package/Search'], function(Language, AcpUiPackageSearch) {
		Language.addObject({
			'wcf.acp.package.install.title': '{lang}wcf.acp.package.install.title{/lang}',
			'wcf.acp.package.update.unauthorized': '{lang}wcf.acp.package.update.unauthorized{/lang}'
		});
		
		new AcpUiPackageSearch();
		
		{if $errorField === 'uploadPackage'}
			elBySel('.jsButtonUploadPackage').click();
		{/if}
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}{@$pageTitle}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="#" class="button jsButtonUploadPackage jsStaticDialog" data-dialog-id="packageUploadDialog"><span class="icon icon16 fa-upload"></span> <span>{lang}wcf.acp.package.upload{/lang}</span></a></li>
			<li><a href="{link controller='PackageList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.package.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if $errorField && $installingImportedStyle}
	<p class="info">{lang}wcf.acp.package.install.installingImportedStyle{/lang}</p>
{/if}

{include file='formError'}

<div class="section">
	<section class="section" id="packageSearch">
		<h2 class="sectionTitle">{lang}wcf.acp.package.search{/lang}</h2>
		
		<dl>
			<dt><label for="packageSearchInput">{lang}wcf.acp.package.search.input{/lang}</label></dt>
			<dd>
				<input type="text" id="packageSearchInput" value="" class="long" autocomplete="off">
				<small>{lang}wcf.acp.package.search.input.description{/lang}</small>
			</dd>
		</dl>
	</section>
	
	<section class="section tabularBox" id="packageSearchResultContainer" data-status="idle">
		<h2 class="sectionTitle">{lang}wcf.acp.package.search.resultList{/lang} <span class="badge" id="packageSearchResultCounter">0</span></h2>
		
		<div class="packageSearchStatus packageSearchStatusIdle">
			<span class="packageSearchStatusLabel">{lang}wcf.acp.package.search.status.idle{/lang}</span>
		</div>
		
		<div class="packageSearchStatus packageSearchStatusRefreshDatabase">
			<span class="icon icon64 fa-spinner"></span>
			<span class="packageSearchStatusLabel">{lang}wcf.acp.package.search.status.refreshDatabase{/lang}</span>
		</div>
		
		<div class="packageSearchStatus packageSearchStatusLoading">
			<span class="icon icon64 fa-spinner"></span>
			<span class="packageSearchStatusLabel">{lang}wcf.acp.package.search.status.loading{/lang}</span>
		</div>
		
		<div class="packageSearchStatus packageSearchStatusNoResults">
			<span class="packageSearchStatusLabel">{lang}wcf.acp.package.search.status.noResults{/lang}</span>
		</div>
		
		<div id="packageSearchResultList"></div>
	</section>
</div>

<div id="packageUploadDialog" class="jsStaticDialogContent" data-title="{lang}wcf.acp.package.upload{/lang}">
	<form method="post" action="{link controller='PackageStartInstall'}{/link}" enctype="multipart/form-data">
		<div class="section">
			<dl{if $errorField == 'uploadPackage'} class="formError"{/if}>
				<dt><label for="uploadPackage">{lang}wcf.acp.package.source.upload{/lang}</label></dt>
				<dd>
					<input type="file" id="uploadPackage" name="uploadPackage" value="" accept="application/x-tar,application/gzip,application/tar+gzip">
					{if $errorField == 'uploadPackage'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
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
			<input type="submit" name="submitButton" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			<input type="hidden" name="action" value="{$action}">
			{@SECURITY_TOKEN_INPUT_TAG}
		</div>
	</form>
</div>

{include file='footer'}
