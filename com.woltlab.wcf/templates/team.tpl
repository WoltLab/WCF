{include file='header'}

{foreach from=$objects->getTeams() item=team}
	<section class="section sectionContainerList">
		<header class="sectionHeader">
			<h2 class="sectionTitle" id="group{@$team->groupID}">{$team->getTitle()} <span class="badge">{#$team->getMembers()|count}</span></h2>
			<p class="sectionDescription">{$team->getDescription()}</p>
		</header>
			
		<ol class="containerList userCardList">
			{foreach from=$team->getMembers() item=user}
				{include file='userCard'}
			{/foreach}
		</ol>
	</section>
{/foreach}

<footer class="contentFooter">
	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}{event name='contentFooterNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

{include file='footer'}
