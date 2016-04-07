{include file='header' pageTitle='wcf.acp.style.list'}

<script data-relocate="true" src="{@$__wcf->getPath()}acp/js/WCF.ACP.Style.js?v={@LAST_UPDATE_TIME}"></script>
<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Toggle('wcf\\data\\style\\StyleAction', '.buttonList');
		new WCF.ACP.Style.List();
	});
	//]]>
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.style.list{/lang}</h1>
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
	<ol class="containerList styleList">
		{foreach from=$objects item=style}
			<li>
				<div class="box64">
					<span><img src="{@$style->getPreviewImage()}" alt="" /></span>
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
							<dd>{if $style->authorURL}<a href="{@$__wcf->getPath()}acp/dereferrer.php?url={$style->authorURL}">{$style->authorName}</a>{else}{$style->authorName}{/if}</dd>
						</dl>
						<nav class="jsMobileNavigation buttonGroupNavigation">
							<ul class="buttonList iconList" data-style-id="{@$style->styleID}">
								<li><a href="{link controller='StyleEdit' id=$style->styleID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a></li>
								<li><a href="{link controller='StyleExport' id=$style->styleID}{/link}" title="{lang}wcf.acp.style.exportStyle{/lang}" class="jsTooltip"><span class="icon icon16 fa-download"></span></a></li>
								
								{if !$style->isDefault}
									<li><span class="icon icon16 fa-{if !$style->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if $style->isDisabled}enable{else}disable{/if}{/lang}" data-object-id="{@$style->styleID}"></span></li>
									<li><a title="{lang}wcf.acp.style.button.setAsDefault{/lang}" class="jsSetAsDefault jsTooltip"><span class="icon icon16 fa-check-circle"></span></a></li>
									<li><a title="{lang}wcf.global.button.delete{/lang}" class="jsDelete jsTooltip" data-confirm-message="{lang}wcf.acp.style.delete.confirmMessage{/lang}"><span class="icon icon16 fa-times"></span></a></li>
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
