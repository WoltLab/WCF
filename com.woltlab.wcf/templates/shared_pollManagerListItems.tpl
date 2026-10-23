{foreach from=$view->getItems() item='poll'}
	<div class="pollManager__item listView__item"
		 role="option"
		 aria-selected="false"
		 tabindex="-1"
		 data-object-id="{$poll->getObjectID()}"
		 data-question="{$poll->getQuestion()}"
	>
		<span class="pollManager__item__indicator" aria-hidden="true">{icon name='check'}</span>

		<div class="pollManager__item__content">
			<div class="pollManager__item__question">{$poll->getQuestion()}</div>
			<div class="pollManager__item__meta">
				{lang}wcf.poll.manager.item.meta{/lang}
			</div>
		</div>

		{hascontent}
			<div class="pollManager__item__menu">
				{content}{unsafe:$view->renderInteractionContextMenuButton($poll)}{/content}
			</div>
		{/hascontent}
	</div>
{/foreach}
