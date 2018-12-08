{if $userTrophyList|count}
	<ol class="containerList jsUserTrophyList">
		{foreach from=$userTrophyList item=userTrophy}
			<li data-object-id="{@$userTrophy->userTrophyID}">
				<div class="box48">
					<div><a href="{link controller='Trophy' object=$userTrophy->getTrophy()}{/link}">{@$userTrophy->getTrophy()->renderTrophy(48)}</a></div>
					
					<div class="containerHeadline">
						<h3><a href="{link controller='Trophy' object=$userTrophy->getTrophy()}{/link}">{$userTrophy->getTrophy()->getTitle()}</a></h3>
						<small>{if !$userTrophy->getDescription()|empty}<span class="separatorRight">{$userTrophy->getDescription()}</span> {/if}{@$userTrophy->time|time}</small>
					</div>
				</div>
			</li>
		{/foreach}
	</ol>

	<div class="paginationBottom jsPagination"></div>
{else}
	<p>{lang}wcf.user.trophy.noTrophies{/lang}</p>
{/if}
