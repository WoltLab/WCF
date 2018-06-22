<div class="section sectionContainerList">
	<ul class="containerList messageSearchResultList">
		{foreach from=$objects item=message}
			<li>
				<div class="box48">
					{assign var=_messageObjectHash value=$message|spl_object_hash}
					{assign var=_messageCustomIcon value=$customIcons[$_messageObjectHash]}
					{if $_messageCustomIcon === ''}
						{if $message->getUserProfile()}
							{if $message->getUserProfile()->userID}
								<a href="{link controller='User' object=$message->getUserProfile()}{/link}" title="{$message->getUserProfile()->username}">{@$message->getUserProfile()->getAvatar()->getImageTag(48)}</a>
							{else}
								<p>{@$message->getUserProfile()->getAvatar()->getImageTag(48)}</p>
							{/if}
						{else}
							<p><span class="icon icon48 fa-file-o"></span></p>
						{/if}
					{elseif $_messageCustomIcon|strpos:'fa-' === 0}
						<p><span class="icon icon48 {$_messageCustomIcon}"></span></p>
					{else}
						<p><img src="{$_messageCustomIcon}" style="width: 48px; height: 48px" alt=""></p>
					{/if}
					
					<div>
						<div class="containerHeadline">
							<h3><a href="{$message->getLink($query)}">{$message->getSubject()}</a></h3>
							
							{if $message->getUserProfile() || $message->getTime() || $message->getContainerTitle()}
								<ul class="inlineList dotSeparated">
									{if $message->getUserProfile()}
										<li>{if $message->getUserProfile()->userID}<a href="{link controller='User' object=$message->getUserProfile()}{/link}" class="userLink" data-user-id="{@$message->getUserProfile()->userID}">{$message->getUserProfile()->username}</a>{else}{$message->getUserProfile()->username}{/if}</li>
									{/if}
									{if $message->getTime()}
										<li><small>{@$message->getTime()|time}</small></li>
									{/if}
									{if $message->getContainerTitle()}
										<li><small><a href="{$message->getContainerLink()}">{$message->getContainerTitle()}</a></small></li>
									{/if}
								</ul>
							{/if}
							<small class="containerContentType">{lang}wcf.search.object.{@$message->getObjectTypeName()}{/lang}</small>
						</div>
						
						<div class="containerContent">{@$message->getFormattedMessage()}</div>
					</div>
				</div>
			</li>
		{/foreach}
	</ul>
</div>
