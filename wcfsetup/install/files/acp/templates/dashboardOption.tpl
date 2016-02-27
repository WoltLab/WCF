{include file='header' pageTitle='wcf.acp.dashboard.option'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init();
	});
	//]]>
</script>

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.dashboard.option{/lang}</h1>
	<p class="contentHeaderDescription">{lang}wcf.dashboard.objectType.{$objectType->objectType}{/lang}</p>
</header>

<p class="info">{lang}wcf.acp.dashboard.box.sort{/lang}</p>

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='DashboardList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.dashboard.list{/lang}</span></a></li>
			
			{event name='contentNavigationButtons'}
		</ul>
	</nav>
</div>

<div class="section tabMenuContainer">
	<nav class="tabMenu">
		<ul>
			{if $objectType->allowcontent}
				<li><a href="{@$__wcf->getAnchor('dashboard-content')}">{lang}wcf.dashboard.boxType.content{/lang}</a></li>
			{/if}
			{if $objectType->allowsidebar}
				<li><a href="{@$__wcf->getAnchor('dashboard-sidebar')}">{lang}wcf.dashboard.boxType.sidebar{/lang}</a></li>
			{/if}
			
			{event name='tabMenuTabs'}
		</ul>
	</nav>
	
	{if $objectType->allowcontent}
		<div id="dashboard-content" class="tabMenuContent hidden">
			<section class="section">
				<h2 class="sectionTitle">{lang}wcf.dashboard.box.enabledBoxes{/lang}</h2>
				
				<div class="sortableListContainer">
					<ol class="sortableList simpleSortableList" data-object-id="0">
						{foreach from=$enabledBoxes item=boxID}
							{if $boxes[$boxID]->boxType == 'content'}
								<li class="sortableNode" data-object-id="{@$boxID}">
									<span class="sortableNodeLabel">{lang}wcf.dashboard.box.{$boxes[$boxID]->boxName}{/lang}{if $boxes[$boxID]->packageID != 1} ({lang}{$boxes[$boxID]->getPackage()->packageName}{/lang}){/if}</span>
								</li>
							{/if}
						{/foreach}
					</ol>
				</div>
			</section>
			
			<section class="section">
				<h2 class="sectionTitle">{lang}wcf.dashboard.box.availableBoxes{/lang}</h2>
				
				<div class="sortableListContainer">
					<ol class="sortableList simpleSortableList">
						{foreach from=$boxes item=box}
							{if $box->boxType == 'content' && !$box->boxID|in_array:$enabledBoxes}
								<li class="sortableNode" data-object-id="{@$box->boxID}">
									<span class="sortableNodeLabel">{lang}wcf.dashboard.box.{$box->boxName}{/lang}{if $box->packageID != 1} ({lang}{$box->getPackage()->packageName}{/lang}){/if}</span>
								</li>
							{/if}
						{/foreach}
					</ol>
				</div>
			</section>
			
			<div class="formSubmit">
				<button data-type="submit">{lang}wcf.global.button.saveSorting{/lang}</button>
			</div>
			
			<script data-relocate="true">
				//<![CDATA[
				$(function() {
					new WCF.Sortable.List('dashboard-content', 'wcf\\data\\dashboard\\box\\DashboardBoxAction', 0, { }, true, { boxType: 'content', objectTypeID: {@$objectTypeID} });
				});
				//]]>
			</script>
		</div>
	{/if}
	
	{if $objectType->allowsidebar}
		<div id="dashboard-sidebar" class="tabMenuContent hidden">
			<section class="section">
				<h2 class="sectionTitle">{lang}wcf.dashboard.box.enabledBoxes{/lang}</h2>
				
				<div class="sortableListContainer">
					<ol class="sortableList simpleSortableList" data-object-id="0">
						{foreach from=$enabledBoxes item=boxID}
							{if $boxes[$boxID]->boxType == 'sidebar'}
								<li class="sortableNode" data-object-id="{@$boxID}">
									<span class="sortableNodeLabel">{lang}wcf.dashboard.box.{$boxes[$boxID]->boxName}{/lang}{if $boxes[$boxID]->packageID != 1} ({lang}{$boxes[$boxID]->getPackage()->packageName}{/lang}){/if}</span>
								</li>
							{/if}
						{/foreach}
					</ol>
				</div>
			</section>
			
			<section class="section">
				<h2 class="sectionTitle">{lang}wcf.dashboard.box.availableBoxes{/lang}</h2>
				
				<div id="dashboard-sidebar-enabled" class="sortableListContainer">
					<ol class="sortableList simpleSortableList">
						{foreach from=$boxes item=box}
							{if $box->boxType == 'sidebar' && !$box->boxID|in_array:$enabledBoxes}
								<li class="sortableNode" data-object-id="{@$box->boxID}">
									<span class="sortableNodeLabel">{lang}wcf.dashboard.box.{$box->boxName}{/lang}{if $box->packageID != 1} ({lang}{$box->getPackage()->packageName}{/lang}){/if}</span>
								</li>
							{/if}
						{/foreach}
					</ol>
				</div>
			</section>
			
			<div class="formSubmit">
				<button data-type="submit">{lang}wcf.global.button.saveSorting{/lang}</button>
			</div>
			
			<script data-relocate="true">
				//<![CDATA[
				$(function() {
					new WCF.Sortable.List('dashboard-sidebar', 'wcf\\data\\dashboard\\box\\DashboardBoxAction', 0, { }, true, { boxType: 'sidebar', objectTypeID: {@$objectTypeID} });
				});
				//]]>
			</script>
		</div>
	{/if}
	
	{event name='tabMenuContents'}
</div>

{include file='footer'}