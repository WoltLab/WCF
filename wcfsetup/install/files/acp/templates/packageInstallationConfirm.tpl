{capture assign='pageTitle'}{lang}wcf.acp.package.{@$queue->action}.title{/lang}: {$archive->getLocalizedPackageInfo('packageName')}{/capture}
{include file='header'}

<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({
			'wcf.acp.package.install.title': '{jslang}wcf.acp.package.install.title{/jslang}',
			'wcf.acp.package.installation.rollback': '{jslang}wcf.acp.package.installation.rollback{/jslang}',
			'wcf.acp.package.uninstallation.title': '{jslang}wcf.acp.package.uninstallation.title{/jslang}',
			'wcf.acp.package.update.title': '{jslang}wcf.acp.package.update.title{/jslang}'
		});
		
		new WCF.ACP.Package.Installation({@$queue->queueID}, undefined, {if $queue->action == 'install'}{if $queue->isApplication}false{else}true{/if}, false{else}false, true{/if});
		
		new WCF.ACP.Package.Installation.Cancel({@$queue->queueID});
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.package.{@$queue->action}.title{/lang}: {$archive->getLocalizedPackageInfo('packageName')}</h1>
		<p class="contentHeaderDescription">{$archive->getLocalizedPackageInfo('packageDescription')}</p>
	</div>
</header>

{if !$validationPassed}
	<p class="error">{lang}wcf.acp.package.validation.failed{/lang}</p>
{/if}

{if $installingImportedStyle}
	<p class="info">{lang}wcf.acp.package.install.installingImportedStyle{/lang}</p>
{/if}

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.acp.package.information.properties{/lang}</h2>
	
	<dl>
		<dt>{lang}wcf.acp.package.identifier{/lang}</dt>
		<dd>{$archive->getPackageInfo('name')}</dd>
	</dl>
	
	<dl>
		<dt>{lang}wcf.acp.package.version{/lang}</dt>
		<dd>{$archive->getPackageInfo('version')}</dd>
	</dl>
	
	<dl>
		<dt>{lang}wcf.acp.package.packageDate{/lang}</dt>
		<dd>{@$archive->getPackageInfo('date')|date}</dd>
	</dl>
	
	{if $archive->getPackageInfo('packageURL') != ''}
		<dl>
			<dt>{lang}wcf.acp.package.url{/lang}</dt>
			<dd><a href="{$archive->getPackageInfo('packageURL')}" class="externalURL">{$archive->getPackageInfo('packageURL')}</a></dd>
		</dl>
	{/if}
	
	<dl>
		<dt>{lang}wcf.acp.package.author{/lang}</dt>
		<dd>{if $archive->getAuthorInfo('authorURL')}<a href="{$archive->getAuthorInfo('authorURL')}" class="externalURL">{$archive->getAuthorInfo('author')}</a>{else}{$archive->getAuthorInfo('author')}{/if}</dd>
	</dl>
	
	{event name='propertyFields'}
</section>

{if !$validationPassed}
	<div class="section tabularBox tabularBoxTitle">
		<header>
			<h2>{lang}wcf.acp.package.validation{/lang}</h2>
		</header>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnTitle columnPackageName">{lang}wcf.acp.package.name{/lang}</th>
					<th class="columnText columnPackage">{lang}wcf.acp.package.identifier{/lang}</th>
					<th class="columnText">{lang}wcf.acp.package.installation.packageStatus{/lang}</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$packageValidationArchives item=packageValidationArchive}
					{assign var=exceptionMessage value=$packageValidationArchive->getExceptionMessage()}
					<tr>
						<td class="columnTitle columnPackageName"><span{if $packageValidationArchive->getDepth()} style="padding-left: {@$packageValidationArchive->getDepth() * 14}px"{/if}>{$packageValidationArchive->getArchive()->getLocalizedPackageInfo('packageName')}</span></td>
						<td class="columnText columnPackage">{$packageValidationArchive->getArchive()->getPackageInfo('name')}</td>
						<td class="columnIcon columnStatus"><span class="icon icon16 {if $exceptionMessage}fa-times-circle red{else}fa-check-circle green{/if}"></span></td>
					</tr>
					
					{if $exceptionMessage}
						<tr>
							<td colspan="3"><span{if $packageValidationArchive->getDepth()} style="padding-left: {@$packageValidationArchive->getDepth() * 14}px"{/if}>{@$exceptionMessage}</span></td>
						</tr>
					{/if}
				{/foreach}
			</tbody>
		</table>
	</div>
{/if}

<div class="formSubmit">
	<input type="button" id="backButton" value="{lang}wcf.global.button.back{/lang}" accesskey="c">
	{if $validationPassed}
		<input type="button" class="default buttonPrimary" id="submitButton" value="{lang}wcf.global.button.next{/lang}" accesskey="s">
	{/if}
</div>

{include file='footer'}
