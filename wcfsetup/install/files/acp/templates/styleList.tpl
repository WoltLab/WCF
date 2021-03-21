{include file='header' pageTitle='wcf.acp.style.list'}

<script data-relocate="true" src="{@$__wcf->getPath()}acp/js/WCF.ACP.Style.js?v={@LAST_UPDATE_TIME}"></script>
<script data-relocate="true">
	$(function() {
		new WCF.ACP.Style.List();
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.style.list{/lang} <span class="badge badgeInverse">{#$items}</span></h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='StyleAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.menu.link.style.add{/lang}</span></a></li>
			<li><a href="{link controller='StyleImport'}{/link}" class="button"><span class="icon icon16 fa-upload"></span> <span>{lang}wcf.acp.menu.link.style.import{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="StyleList" link="pageNo=%d"}{/content}
	</div>
{/hascontent}

<div class="section sectionContainerList">
	<ol class="containerList styleList jsObjectActionContainer" data-object-action-class-name="wcf\data\style\StyleAction">
		{foreach from=$objects item=style}
			<li class="jsObjectActionObject" data-object-id="{@$style->getObjectID()}">
				<div class="box64">
					<span>
						<img src="{@$style->getPreviewImage()}" srcset="{@$style->getPreviewImage2x()} 2x" height="64" alt="">
					</span>
					<div class="details">
						<div class="containerHeadline">
							<h3><a href="{link controller='StyleEdit' id=$style->styleID}{/link}">{$style->styleName}</a></h3>
							{if $style->styleDescription}<small>{lang}{@$style->styleDescription}{/lang}</small>{/if}
						</div>
						<dl class="plain inlineDataList">
							<dt>{lang}wcf.acp.style.users{/lang}</dt>
							<dd>{#$style->users}</dd>
						</dl>
						<dl class="plain inlineDataList">
							<dt>{lang}wcf.acp.style.styleVersion{/lang}</dt>
							<dd>{$style->styleVersion} ({$style->styleDate})</dd>
						</dl>
						<dl class="plain inlineDataList">
							<dt>{lang}wcf.acp.style.authorName{/lang}</dt>
							<dd>{if $style->authorURL}<a href="{$style->authorURL}">{$style->authorName}</a>{else}{$style->authorName}{/if}</dd>
						</dl>
						<nav class="jsMobileNavigation buttonGroupNavigation">
							<ul class="buttonList iconList" data-style-id="{@$style->styleID}">
								<li><a href="{link controller='StyleEdit' id=$style->styleID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span> <span class="invisible">{lang}wcf.global.button.edit{/lang}</span></a></li>
								<li><a href="{link controller='StyleExport' id=$style->styleID}{/link}" title="{lang}wcf.acp.style.exportStyle{/lang}" class="jsTooltip"><span class="icon icon16 fa-download"></span> <span class="invisible">{lang}wcf.acp.style.exportStyle{/lang}</span></a></li>
								
								{if !$style->isDefault}
									<li><a href="#" title="{lang}wcf.global.button.{if $style->isDisabled}enable{else}disable{/if}{/lang}" class="jsTooltip"><span class="icon icon16 fa-{if !$style->isDisabled}check-{/if}square-o jsObjectAction" data-object-action="toggle"></span> <span class="invisible">{lang}wcf.global.button.{if $style->isDisabled}enable{else}disable{/if}{/lang}</span></a></li>
									<li><a href="#" title="{lang}wcf.acp.style.button.setAsDefault{/lang}" class="jsSetAsDefault jsTooltip"><span class="icon icon16 fa-check-circle"></span> <span class="invisible">{lang}wcf.acp.style.button.setAsDefault{/lang}</span></a></li>
									<li><a href="#" title="{lang}wcf.global.button.delete{/lang}" class="jsDelete jsTooltip" data-confirm-message-html="{lang __encode=true}wcf.acp.style.delete.confirmMessage{/lang}"><span class="icon icon16 fa-times"></span> <span class="invisible">{lang}wcf.global.button.delete{/lang}</span></a></li>
								{/if}
								
								{event name='itemButtons'}
							</ul>
						</nav>
					</div>
				</div>
			</li>
		{/foreach}
	</ol>
</div>

<footer class="contentFooter">
	{hascontent}
		<div class="paginationBottom">
			{content}{@$pagesLinks}{/content}
		</div>
	{/hascontent}
	
	<nav class="contentFooterNavigation">
		<ul>
			<li><a href="{link controller='StyleAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.menu.link.style.add{/lang}</span></a></li>
			<li><a href="{link controller='StyleImport'}{/link}" class="button"><span class="icon icon16 fa-upload"></span> <span>{lang}wcf.acp.menu.link.style.import{/lang}</span></a></li>
			
			{event name='contentFooterNavigation'}
		</ul>
	</nav>
</footer>

{include file='footer'}
