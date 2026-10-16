{foreach from=$view->getItems() item='userTrophy'}
	{assign var='trophy' value=$userTrophy->getTrophy()}
	<div class="userTrophyList__item listView__item" data-object-id="{$userTrophy->getObjectID()}">
		<div class="userTrophyList__item__icon">
			<a href="{$trophy->getLink()}">{unsafe:$trophy->renderTrophy(48)}</a>
		</div>

		<div class="userTrophyList__item__content">
			<h3 class="userTrophyList__item__title">
				<a href="{$trophy->getLink()}" class="userTrophyList__item__link">{$trophy->getTitle()}</a>
			</h3>
			<div class="userTrophyList__item__description">
				{if !$userTrophy->getDescription()|empty}<span class="separatorRight">{unsafe:$userTrophy->getDescription()}</span> {/if}{time time=$userTrophy->time}
			</div>
		</div>
	</div>
{/foreach}
