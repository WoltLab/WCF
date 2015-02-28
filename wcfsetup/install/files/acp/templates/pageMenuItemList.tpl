{include file='header' pageTitle='wcf.acp.pageMenu.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\page\\menu\\item\\PageMenuItemAction', '.sortableNode', '> .sortableNodeLabel .jsDeleteButton');
		new WCF.Action.Toggle('wcf\\data\\page\\menu\\item\\PageMenuItemAction', '.sortableNode', '> .sortableNodeLabel .jsToggleButton');
		
		{if $headerItems|count}
			new WCF.Sortable.List('pageMenuItemHeaderList', 'wcf\\data\\page\\menu\\item\\PageMenuItemAction', undefined, { protectRoot: true }, false, { menuPosition: 'header' });
		{/if}
		{if $footerItems|count}
			new WCF.Sortable.List('pageMenuItemFooterList', 'wcf\\data\\page\\menu\\item\\PageMenuItemAction', undefined, { }, true, { menuPosition: 'footer' });
		{/if}
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.pageMenu.list{/lang}</h1>
</header>

<p class="info">{lang}wcf.acp.pageMenu.landingPage.description{/lang}</p>

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='PageMenuItemAdd'}{/link}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.pageMenu.add{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{hascontent}
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.acp.pageMenu.header{/lang}</legend>
			
			<div id="pageMenuItemHeaderList" class="sortableListContainer">
				<ol class="sortableList" data-object-id="0">
					{content}
						{foreach from=$headerItems item=menuItem}
							<li class="sortableNode" data-object-id="{@$menuItem->menuItemID}">
								<span class="sortableNodeLabel">
									<a href="{link controller='PageMenuItemEdit' id=$menuItem->menuItemID}{/link}">{lang}{$menuItem->menuItem}{/lang}</a>
									<span class="statusDisplay sortableButtonContainer">
										{if $menuItem->canDisable()}
											<span class="icon icon16 icon-check{if $menuItem->isDisabled}-empty{/if} jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if $menuItem->isDisabled}enable{else}disable{/if}{/lang}" data-object-id="{@$menuItem->menuItemID}"></span>
										{else}
											<span class="icon icon16 icon-check{if $menuItem->isDisabled}-empty{/if} disabled" title="{lang}wcf.global.button.{if $menuItem->isDisabled}enable{else}disable{/if}{/lang}"></span>
										{/if}
										<a href="{link controller='PageMenuItemEdit' id=$menuItem->menuItemID}{/link}" class="jsTooltip" title="{lang}wcf.global.button.edit{/lang}"><span class="icon icon16 icon-pencil"></span></a>
										{if $menuItem->canDelete()}
											<span class="icon icon16 icon-remove jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$menuItem->menuItemID}" data-confirm-message="{lang __menuItem=$menuItem}wcf.acp.pageMenu.delete.sure{/lang}"></span>
										{else}
											<span class="icon icon16 icon-remove disabled" title="{lang}wcf.global.button.delete{/lang}"></span>
										{/if}
										
										{event name='headerItemButtons'}
									</span>
								</span>
								{if $menuItem|count}
									<ol class="sortableList" data-object-id="{@$menuItem->menuItemID}">
										{foreach from=$menuItem item=childMenuItem}
											<li class="sortableNode sortableNoNesting" data-object-id="{@$childMenuItem->menuItemID}">
												<span class="sortableNodeLabel">
													<a href="{link controller='PageMenuItemEdit' id=$childMenuItem->menuItemID}{/link}">{lang}{$childMenuItem->menuItem}{/lang}</a>
													<span class="statusDisplay sortableButtonContainer">
														<span class="icon icon16 icon-check{if $childMenuItem->isDisabled}-empty{/if} jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if $childMenuItem->isDisabled}enable{else}disable{/if}{/lang}" data-object-id="{@$childMenuItem->menuItemID}"></span>
														<a href="{link controller='PageMenuItemEdit' id=$childMenuItem->menuItemID}{/link}" class="jsTooltip" title="{lang}wcf.global.button.edit{/lang}"><span class="icon icon16 icon-pencil"></span></a>
														{if $childMenuItem->canDelete()}
															<span class="icon icon16 icon-remove jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$childMenuItem->menuItemID}" data-confirm-message="{lang __menuItem=$childMenuItem}wcf.acp.pageMenu.delete.sure{/lang}"></span>
														{else}
															<span class="icon icon16 icon-remove disabled" title="{lang}wcf.global.button.delete{/lang}"></span>
														{/if}
														
														{event name='subHeaderItemButtons'}
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
	</div>
{/hascontent}

{hascontent}
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.acp.pageMenu.footer{/lang}</legend>
			
			<div id="pageMenuItemFooterList" class="sortableListContainer">
				<ol class="sortableList simpleSortableList" data-object-id="0">
					{content}
						{foreach from=$footerItems item=menuItem}
							<li class="sortableNode" data-object-id="{@$menuItem->menuItemID}">
								<span class="sortableNodeLabel">
									<a href="{link controller='PageMenuItemEdit' id=$menuItem->menuItemID}{/link}">{lang}{$menuItem->menuItem}{/lang}</a>
									<span class="statusDisplay sortableButtonContainer">
										<span class="icon icon16 icon-check{if $menuItem->isDisabled}-empty{/if} jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if $menuItem->isDisabled}enable{else}disable{/if}{/lang}" data-object-id="{@$menuItem->menuItemID}"></span>
										<a href="{link controller='PageMenuItemEdit' id=$menuItem->menuItemID}{/link}" class="jsTooltip" title="{lang}wcf.global.button.edit{/lang}"><span class="icon icon16 icon-pencil"></span></a>
										{if $menuItem->canDelete()}
											<span class="icon icon16 icon-remove jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$menuItem->menuItemID}" data-confirm-message="{lang __menuItem=$menuItem}wcf.acp.pageMenu.delete.sure{/lang}"></span>
										{else}
											<span class="icon icon16 icon-remove disabled" title="{lang}wcf.global.button.delete{/lang}"></span>
										{/if}
										
										{event name='footerItemButtons'}
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
	</div>
{/hascontent}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='PageMenuItemAdd'}{/link}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.pageMenu.add{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsBottom'}
		</ul>
	</nav>
</div>

{include file='footer'}
