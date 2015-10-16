<nav id="mainMenu" class="mainMenu jsMobileNavigation" data-button-label="{lang}wcf.page.mainMenu{/lang}">
	<ul>
		<li>
			<a href="#" id="daytay"><span class="icon icon24 fa-bars"></span> Menu</a>
			
			<ol id="dtdesign">
				<li id="leftColumnContainer">
					<ol id="leftColumn" class="menuItemList"></ol>
				</li>
				<li id="centerColumnContainer"></li>
				<li id="rightColumnContainer"></li>
			</ol>
		</li>
	</ul>
</nav>

{* work-around for unknown core-object during WCFSetup *}
{*{if PACKAGE_ID && $__wcf->user->userID}
	<nav id="mainMenu" class="mainMenu jsMobileNavigation" data-button-label="{lang}wcf.page.mainMenu{/lang}">
		<ul>
			{foreach from=$__wcf->getACPMenu()->getMenuItems('') item=_parentMenuItem}
				<li class="subMenuItems">
					<a href="#">{lang}{@$_parentMenuItem->menuItem}{/lang}</a>
					
					<ol class="subMenu">
						{foreach from=$__wcf->getACPMenu()->getMenuItems($_parentMenuItem->menuItem) item=_menuItem}
							<li>
								<a href="#">{@$_menuItem}</a>
								
								<ol class="dtdesign">
									{foreach from=$__wcf->getACPMenu()->getMenuItems($_menuItem->menuItem) item=menuItemCategory}
										{if $__wcf->getACPMenu()->getMenuItems($menuItemCategory->menuItem)|count > 0}
											{foreach from=$__wcf->getACPMenu()->getMenuItems($menuItemCategory->menuItem) item=subMenuItem}
												<li id="{$subMenuItem->menuItem}" data-menu-item="{$subMenuItem->menuItem}"><a href="{$subMenuItem->getLink()}">{@$subMenuItem}</a></li>
											{/foreach}
										{else}
											<li id="{$menuItemCategory->menuItem}" data-menu-item="{$menuItemCategory->menuItem}"><a href="{$menuItemCategory->getLink()}">{@$menuItemCategory}</a></li>
										{/if}
									{/foreach}
								</ol>
							</li>
						{/foreach}
					</ol>
				</li>
			{/foreach}
		</ul>
	</nav>
{/if}*}

<script>
var structure = [], menuCategory, category;

{foreach from=$__wcf->getACPMenu()->getMenuItems('') item=_parentMenuItem}
	menuCategory = {
		label: '{lang}{@$_parentMenuItem->menuItem}{/lang}',
		items: []
	};

	{foreach from=$__wcf->getACPMenu()->getMenuItems($_parentMenuItem->menuItem) item=_menuItem}
		category = {
			label: '{@$_menuItem}',
			items: []
		};

		{foreach from=$__wcf->getACPMenu()->getMenuItems($_menuItem->menuItem) item=menuItemCategory}
			{if $__wcf->getACPMenu()->getMenuItems($menuItemCategory->menuItem)|count > 0}
				{foreach from=$__wcf->getACPMenu()->getMenuItems($menuItemCategory->menuItem) item=subMenuItem}
					category.items.push({
						label: '{@$subMenuItem}',
						link: '{$subMenuItem->getLink()}'
					});
				{/foreach}
			{else}
				category.items.push({
					label: '{@$menuItemCategory}',
					link: '{$menuItemCategory->getLink()}'
				});
			{/if}
		{/foreach}

		menuCategory.items.push(category);
	{/foreach}
	
	structure.push(menuCategory);
{/foreach}

const LEFT = 1;
const CENTER = 2;
const RIGHT = 3;

function getList(position, isContainer) {
	var list;
	if (position === LEFT) {
		list = 'left';
	}
	else {
		list = (position === CENTER) ? 'center' : 'right';
	}
	
	return document.getElementById(list + 'Column' + (isContainer === true ? 'Container' : ''));
}

function createListItem(position, label, data, isSubCategory, link) {
	var list = (typeof position === 'object') ? position : getList(position);
	
	var listItem = document.createElement('li');
	var linkEl = document.createElement('a');
	linkEl.textContent = label;
	linkEl.href = (link ? link : '#');
	
	listItem.appendChild(linkEl);
	list.appendChild(listItem);
	
	if (!link) {
		linkEl.addEventListener('click', function(e) {
			e.preventDefault();
			
			showCategories(data, isSubCategory);
			
			markAsActive(this);
		})
	}
}

function markAsActive(element) {
	var list = element.parentNode.parentNode;
	var item, items = list.querySelectorAll('LI');
	for (var i = 0, length = items.length; i < length; i++) {
		item = items[i];
		console.debug(item);
		item.classList[(item === element.parentNode ? 'add' : 'remove')]('active2');
	}
}

var x;
for (var i = 0, length = structure.length; i < length; i++) {
	x = structure[i];
	
	createListItem(LEFT, x.label, x.items);
}
	
function showCategories(items, isSubCategory) {
	var right = getList(RIGHT, true);
	right.innerHTML = '';
	
	var center = getList(CENTER, true);
	if (isSubCategory !== true) center.innerHTML = '';
	
	list = document.createElement('ol');
	list.className = 'menuItemList';
	list.id = 'centerColumn';
	
	var item;
	for (var i = 0, length = items.length; i < length; i++) {
		item = items[i];
		
		createListItem(list, item.label, item.items, true, (typeof item.link === 'string') ? item.link : '');
	}
	
	(isSubCategory === true ? right : center).appendChild(list);
}
</script>

<style>
	#mainMenu {
		position: relative;
	}
	
	#mainMenu .icon {
		color: rgb(255, 255, 255);
	}
	
	#dtdesign {
		background-color: #fff;
		border: 1px solid rgb(44, 62, 80);
		box-shadow: 2px 2px 10px 0 rgba(0, 0, 0, .2);
		display: flex;
		position: absolute;
		top: 51px;
	}
	
	#dtdesign > li {
		flex: 0 auto;
	}
	
	#dtdesign > li:not(:empty) {
		border-left: 1px solid rgb(238, 238, 238);
	}
	
	#dtdesign > li:first-child {
		border-left-width: 0;
	}
	
	.menuItemList {
		min-width: 250px;
	}
	
	.menuItemList > li > a {
		color: rgb(54, 54, 54);
		display: block;
		font-size: 1rem;
		padding: 10px 40px 10px 20px;
		white-space: nowrap;
	}
	
	.menuItemList > li.active2 > a {
		background-color: rgb(189, 195, 199);
	}
	
	.menuItemList > li > a:hover {
		background-color: rgb(66, 129, 244);
		color: #fff;
		text-decoration: none;
	}
	
	.menuItemList > li > a[href="#"] {
		position: relative;
	}
	
	.menuItemList > li > a[href="#"]::after {
		content: "\f105";
		font-family: FontAwesome;
		position: absolute;
		right: 20px;
		top: 50%;
		transform: translateY(-50%);
	}
	
	.menuItemList > li.active2 > a[href="#"]::after {
		content: "\f104"
	}
</style>
