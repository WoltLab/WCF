{if PACKAGE_ID}{assign var=_activeMenuItems value=$__wcf->getACPMenu()->getActiveMenuItems()}{/if}
<nav id="mainMenu" class="mainMenu jsMobileNavigation" data-button-label="{lang}wcf.page.mainMenu{/lang}">
	{if PACKAGE_ID}
		<ul>
			{foreach from=$__wcf->getACPMenu()->getMenuItems('') item=_sectionMenuItem}
				<li class="subMenuItems{if $_sectionMenuItem->menuItem|in_array:$_activeMenuItems} active{/if}" data-menu-item="{$_sectionMenuItem->menuItem}">
					<a>{@$_sectionMenuItem}</a>
					
					{assign var=_menuItemCategories value=$__wcf->getACPMenu()->getMenuItems($_sectionMenuItem->menuItem)}
					<ol class="wcfAcpMenu subMenu{if $_menuItemCategories|count > 3} doubleColumned {/if}">
						{foreach from=$_menuItemCategories item=_menuItemCategory}
							<li>
								<span>{@$_menuItemCategory}</span>
								
								<ol class="menuItemList">
									{foreach from=$__wcf->getACPMenu()->getMenuItems($_menuItemCategory->menuItem) item=_menuItem}
										{assign var=_subMenuItems value=$__wcf->getACPMenu()->getMenuItems($_menuItem->menuItem)}
										
										{if $_subMenuItems|empty}
											<li{if $_menuItem->menuItem|in_array:$_activeMenuItems} class="active"{/if}><a href="{$_menuItem->getLink()}">{@$_menuItem}</a></li>
										{else}
											{if $_menuItemCategory->menuItem === 'wcf.acp.menu.link.option'}
											{* handle special option categories *}
												{foreach from=$_subMenuItems item=_subMenuItem}
													<li{if $_subMenuItem->menuItem|in_array:$_activeMenuItems} class="active"{/if}><a href="{$_subMenuItem->getLink()}">{@$_subMenuItem}</a></li>
												{/foreach}
											{else}
												<li>
													<div class="menuItemWrapper">
														<a href="{$_menuItem->getLink()}"{if $_menuItem->menuItem|in_array:$_activeMenuItems && $_activeMenuItems[0] === $_menuItem->menuItem} class="active"{/if}>{@$_menuItem}</a>
														
														{foreach from=$_subMenuItems item=_subMenuItem}
															<a href="{$_subMenuItem->getLink()}" class="jsTooltip{if $_subMenuItem->menuItem|in_array:$_activeMenuItems} active{/if}" title="{@$_subMenuItem}"><span class="icon icon16 {$_subMenuItem->icon}"></span></a>
														{/foreach}
													</div>
												</li>
											{/if}
										{/if}
									{/foreach}
								</ol>
							</li>
						{/foreach}
					</ol>
				</li>
			{/foreach}
		</ul>
	{/if}
</nav>
