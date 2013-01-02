{include file='header' pageTitle='wcf.acp.style.list'}

<script type="text/javascript" src="{@$__wcf->getPath()}acp/js/WCF.ACP.Style.js"></script>
<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.Action.Toggle('wcf\\data\\style\\StyleAction', '.buttonList');
		new WCF.ACP.Style.List();
	});
	//]]>
</script>

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.style.list{/lang}</h1>
	</hgroup>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="StyleList" link="pageNo=%d"}
	
	{hascontent}
		<nav>
			<ul>
				{content}
					{if $__wcf->session->getPermission('admin.style.canAddStyle')}
						<li><a href="{link controller='StyleAdd'}{/link}" title="{lang}wcf.acp.menu.link.style.add{/lang}" class="button"><img src="{@$__wcf->getPath()}icon/add.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.menu.link.style.add{/lang}</span></a></li>
						<li><a href="{link controller='StyleImport'}{/link}" title="{lang}wcf.acp.menu.link.style.import{/lang}" class="button"><img src="{@$__wcf->getPath()}icon/upload.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.menu.link.style.import{/lang}</span></a></li>
					{/if}
					
					{event name='largeButtons'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

<div class="container marginTop">
	<ol class="containerList styleList">
		{foreach from=$objects item=style}
			<li>
				<div class="box64">
					<span class="framed"><img src="{@$style->getPreviewImage()}" alt="" /></span>
					<div class="details">
						<hgroup class="containerHeadline">
							<h1><a href="{link controller='StyleEdit' id=$style->styleID}{/link}">{$style->styleName}</a></h1>
							{if $style->styleDescription}<h2>{$style->styleDescription}</h2>{/if}
						</hgroup>
						<ul class="buttonList" data-style-id="{@$style->styleID}">
							<li><a href="{link controller='StyleEdit' id=$style->styleID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><img src="{@$__wcf->getPath()}icon/edit.svg" class="icon16" alt="" /></a></li>
							{if !$style->isDefault}
								<li><img src="{@$__wcf->getPath()}icon/{if $style->isDisabled}disabled{else}enabled{/if}.svg" title="{lang}wcf.global.button.{if $style->isDisabled}enable{else}disable{/if}{/lang}" alt="" class="icon16 jsToggleButton jsTooltip" data-object-id="{@$style->styleID}" /></li>
								<li><a title="{lang}wcf.acp.style.button.setAsDefault{/lang}" class="jsSetAsDefault jsTooltip"><img src="{@$__wcf->getPath()}icon/default.svg" class="icon16 jsTooltip" alt="" /></a></li>
								<li><a title="{lang}wcf.global.button.delete{/lang}" class="jsDelete jsTooltip" data-confirm-message="{lang}wcf.acp.style.delete.confirmMessage{/lang}"><img src="{@$__wcf->getPath()}icon/delete.svg" class="icon16" alt="" /></a></li>
							{/if}
						</ul>
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
					</div>
				</div>
			</li>
		{/foreach}
	</ol>
</div>

<div class="contentNavigation">
	{@$pagesLinks}
	
	{hascontent}
		<nav>
			<ul>
				{content}
					{if $__wcf->session->getPermission('admin.style.canAddStyle')}
						<li><a href="{link controller='StyleAdd'}{/link}" title="{lang}wcf.acp.menu.link.style.add{/lang}" class="button"><img src="{@$__wcf->getPath()}icon/add.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.menu.link.style.add{/lang}</span></a></li>
						<li><a href="{link controller='StyleImport'}{/link}" title="{lang}wcf.acp.menu.link.style.import{/lang}" class="button"><img src="{@$__wcf->getPath()}icon/upload.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.menu.link.style.import{/lang}</span></a></li>
					{/if}
					
					{event name='largeButtons'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{include file='footer'}
