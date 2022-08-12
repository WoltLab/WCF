<div class="section sectionContainerList">
	<ul class="gridList messageSearchResultList">
		{foreach from=$objects item=message}
			<li class="gridListItem gridListItemMessage">
				<div class="gridListItemImage">
					{assign var=_messageCustomIcon value=$customIcons[$message]}
					{if $_messageCustomIcon === ''}
						{if $message->getUserProfile()}
							{user object=$message->getUserProfile() type='avatar48' ariaHidden='true' tabindex='-1'}
						{else}
							{icon size=48 name='file'}
						{/if}
					{elseif $_messageCustomIcon|strpos:'fa-' === 0}
						<span class="icon icon48 {$_messageCustomIcon}"></span>
					{else}
						<img src="{$_messageCustomIcon}" height="48" width="48" alt="">
					{/if}
				</div>
				
				<h3 class="gridListItemTitle">
					<a href="{$message->getLink($query)}">{$message->getSubject()}</a>
				</h3>

				{hascontent}
				<div class="gridListItemMeta">
					<ul class="inlineList dotSeparated">
						{content}
							{if $message->getUserProfile()}
								<li>{user object=$message->getUserProfile()}</li>
							{/if}
							{if $message->getTime()}
								<li><small>{@$message->getTime()|time}</small></li>
							{/if}
							{if $message->getContainerTitle()}
								<li><small><a href="{$message->getContainerLink()}">{$message->getContainerTitle()}</a></small></li>
							{/if}
						{/content}
					</ul>
				</div>
				{/hascontent}

				<small class="gridListItemType">{lang}wcf.search.object.{@$message->getObjectTypeName()}{/lang}</small>
				
				<div class="gridListItemContent">{@$message->getFormattedMessage()}</div>
			</li>
		{/foreach}
	</ul>
</div>
