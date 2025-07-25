{include file='header'}

{foreach from=$teams item='teamData'}
	{assign var='team' value=$teamData['team']}
	{assign var='listView' value=$teamData['listView']}
	
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle" id="group{$team->groupID}">{$team->getTitle()} <span class="badge">{#$listView->countItems()}</span></h2>
			<p class="sectionDescription">{$team->getDescription()}</p>
		</header>
		
		<div class="{$listView->getContainerCssClassName()}">
			{unsafe:$listView->render()}
		</div>
	</section>
{/foreach}

{include file='footer'}
