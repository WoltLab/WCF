{if $boxPosition == 'sidebarLeft' || $boxPosition == 'sidebarRight'}
	<ul class="sidebarItemList">
		{foreach from=$boxUserTrophyList item=boxUserTrophy}
			<li class="box32">
				<div>{@$boxUserTrophy->getTrophy()->renderTrophy(32)}</div>

				<div class="sidebarItemTitle">
					<h3>
						<a href="{$boxUserTrophy->getTrophy()->getLink()}">{$boxUserTrophy->getTrophy()->getTitle()}</a>
					</h3>
					<small>
						{@$boxUserTrophy->getUserProfile()->getAnchorTag()}
						<span class="separatorLeft">{@$boxUserTrophy->time|time}</span>
					</small>
				</div>
			</li>
		{/foreach}
	</ul>
{else}
	<ol class="containerBoxList trophyCategoryList tripleColumned">
		{foreach from=$boxUserTrophyList item=boxUserTrophy}
			<li class="box64">
				<div>{@$boxUserTrophy->getTrophy()->renderTrophy(64)}</div>

				<div class="sidebarItemTitle">
					<h3><a href="{$boxUserTrophy->getTrophy()->getLink()}">{$boxUserTrophy->getTrophy()->getTitle()}</a></h3>
					<small>{if !$boxUserTrophy->getDescription()|empty}<p>{@$boxUserTrophy->getDescription()}</p>{/if}<p>{@$boxUserTrophy->getUserProfile()->getAnchorTag()} <span class="separatorLeft">{@$boxUserTrophy->time|time}</span></p></small>
				</div>
			</li>
		{/foreach}
	</ol>
{/if}
