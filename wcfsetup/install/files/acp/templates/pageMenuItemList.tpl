{include file='header' pageTitle='wcf.acp.pageMenu.list'}

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.pageMenu.list{/lang}</h1>
	</hgroup>
</header>

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\page\\menu\\item\\PageMenuItemAction', '.sortableNode');
		new WCF.Action.Toggle('wcf\\data\\page\\menu\\item\\PageMenuItemAction', $('.sortableNode'));
		
		{if $headerItems|count}new WCF.Sortable.List('pageMenuItemHeaderList', 'wcf\\data\\page\\menu\\item\\PageMenuItemAction', undefined, { protectRoot: true }, false, { menuPosition: 'header' });{/if}
		{if $footerItems|count}new WCF.Sortable.List('pageMenuItemFooterList', 'wcf\\data\\page\\menu\\item\\PageMenuItemAction', undefined, { }, true, { menuPosition: 'footer' });{/if}
	});
	//]]>
</script>

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='PageMenuItemAdd'}{/link}" title="{lang}wcf.acp.pageMenu.add{/lang}" class="button"><img src="{@$__wcf->getPath()}icon/add.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.pageMenu.add{/lang}</span></a></li>
			
			{event name='largeButtonsTop'}
		</ul>
	</nav>
</div>

{hascontent}
	<fieldset>
		<legend>{lang}wcf.acp.pageMenu.header{/lang}</legend>
		
		<div id="pageMenuItemHeaderList" class="container containerPadding sortableListContainer">
			<ol class="sortableList" data-object-id="0">
				{content}
					{foreach from=$headerItems item=menuItem}
						<li class="sortableNode" data-object-id="{@$menuItem->menuItemID}">
							<span class="sortableNodeLabel">
								<a href="{link controller='PageMenuItemEdit' id=$menuItem->menuItemID}{/link}">{lang}{$menuItem->menuItem}{/lang}</a>
								<span class="statusDisplay sortableButtonContainer">
									<img src="{@$__wcf->getPath()}icon/{if $menuItem->isDisabled}disabled{else}enabled{/if}.svg" alt="" title="{lang}wcf.global.button.{if $menuItem->isDisabled}enable{else}disable{/if}{/lang}" class="icon16 jsToggleButton jsTooltip pointer" data-object-id="{@$menuItem->menuItemID}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" />
									<a href="{link controller='PageMenuItemEdit' id=$menuItem->menuItemID}{/link}" class="jsTooltip" title="{lang}wcf.global.button.edit{/lang}"><img src="{@$__wcf->getPath()}icon/edit.svg" alt="" class="icon16" /></a>
									<img src="{@$__wcf->getPath()}icon/delete.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" class="icon16 jsDeleteButton jsTooltip pointer" data-object-id="{@$menuItem->menuItemID}" data-confirm-message="{lang __menuItem=$menuItem}wcf.acp.pageMenu.delete.sure{/lang}" />
								</span>
							</span>
							{if $menuItem|count}
								<ol class="sortableList" data-object-id="{@$menuItem->menuItemID}">
									{foreach from=$menuItem item=childMenuItem}
										<li class="sortableNode sortableNoNesting" data-object-id="{@$childMenuItem->menuItemID}">
											<span class="sortableNodeLabel">
												<a href="{link controller='PageMenuItemEdit' id=$childMenuItem->menuItemID}{/link}">{lang}{$childMenuItem->menuItem}{/lang}</a>
												<span class="statusDisplay sortableButtonContainer">
													<img src="{@$__wcf->getPath()}icon/{if $childMenuItem->isDisabled}disabled{else}enabled{/if}.svg" alt="" title="{lang}wcf.global.button.{if $childMenuItem->isDisabled}enable{else}disable{/if}{/lang}" class="icon16 jsToggleButton jsTooltip pointer" data-object-id="{@$childMenuItem->menuItemID}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" />
													<a href="{link controller='PageMenuItemEdit' id=$childMenuItem->menuItemID}{/link}" class="jsTooltip" title="{lang}wcf.global.button.edit{/lang}"><img src="{@$__wcf->getPath()}icon/edit.svg" alt="" class="icon16" /></a>
													<img src="{@$__wcf->getPath()}icon/delete.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" class="icon16 jsDeleteButton jsTooltip pointer" data-object-id="{@$childMenuItem->menuItemID}" data-confirm-message="{lang __menuItem=$childMenuItem}wcf.acp.pageMenu.delete.sure{/lang}" />
												</span>
											</span>
										</li>
									{/foreach}
								</ol>
							{/if}
						</li>
					{/foreach}
				{/content}
			</ol>
			
			<div class="formSubmit">
				<button data-type="submit">{lang}wcf.global.button.saveSorting{/lang}</button>
			</div>
		</div>
	</fieldset>
{/hascontent}

{hascontent}
	<fieldset>
		<legend>{lang}wcf.acp.pageMenu.footer{/lang}</legend>
		
		<div id="pageMenuItemFooterList" class="container containerPadding sortableListContainer">
			<ol class="sortableList simpleSortableList">
				{content}
					{foreach from=$footerItems item=menuItem}
						<li class="sortableNode" data-object-id="{@$menuItem->menuItemID}">
							<span class="sortableNodeLabel">
								<a href="{link controller='PageMenuItemEdit' id=$menuItem->menuItemID}{/link}">{lang}{$menuItem->menuItem}{/lang}</a>
								<span class="statusDisplay sortableButtonContainer">
									<img src="{@$__wcf->getPath()}icon/{if $menuItem->isDisabled}disabled{else}enabled{/if}.svg" alt="" title="{lang}wcf.global.button.{if $menuItem->isDisabled}enable{else}disable{/if}{/lang}" class="icon16 jsToggleButton jsTooltip pointer" data-object-id="{@$menuItem->menuItemID}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" />
									<a href="{link controller='PageMenuItemEdit' id=$menuItem->menuItemID}{/link}" class="jsTooltip" title="{lang}wcf.global.button.edit{/lang}"><img src="{@$__wcf->getPath()}icon/edit.svg" alt="" class="icon16" /></a>
									<img src="{@$__wcf->getPath()}icon/delete.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" class="icon16 jsDeleteButton jsTooltip pointer" data-object-id="{@$menuItem->menuItemID}" data-confirm-message="{lang}wcf.acp.pageMenu.delete.sure{/lang}" />
								</span>
							</span>
						</li>
					{/foreach}
				{/content}
			</ol>
			
			<div class="formSubmit">
				<button data-type="submit">{lang}wcf.global.button.saveSorting{/lang}</button>
			</div>
		</div>
	</fieldset>
{/hascontent}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='PageMenuItemAdd'}{/link}" title="{lang}wcf.acp.pageMenu.add{/lang}" class="button"><img src="{@$__wcf->getPath()}icon/add.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.pageMenu.add{/lang}</span></a></li>
			
			{event name='largeButtonsBottom'}
		</ul>
	</nav>
</div>

{include file='footer'}
