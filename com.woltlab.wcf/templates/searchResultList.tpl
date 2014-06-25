<div class="container marginTop">
	<ul class="containerList messageSearchResultList">
		{foreach from=$objects item=message}
			<li>
				<div class="box48">
					{if $message->getUserProfile()}
						{if $message->getUserProfile()->userID}
							<a href="{link controller='User' object=$message->getUserProfile()}{/link}" title="{$message->getUserProfile()->username}" class="framed">{@$message->getUserProfile()->getAvatar()->getImageTag(48)}</a>
						{else}
							<p class="framed">{@$message->getUserProfile()->getAvatar()->getImageTag(48)}</p>
						{/if}
					{else}
						<p class="framed"><span class="icon icon48 fa-file-o"></span></p>
					{/if}
					
					<div>
						<div class="containerHeadline">
							<h3><a href="{$message->getLink($query)}">{$message->getSubject()}</a></h3>
							
							{if $message->getUserProfile() || $message->getTime() || $message->getContainerTitle()}
								<p>
									{if $message->getUserProfile()}
										{if $message->getUserProfile()->userID}
											<a href="{link controller='User' object=$message->getUserProfile()}{/link}" class="userLink" data-user-id="{@$message->getUserProfile()->userID}">{$message->getUserProfile()->username}</a>
										{else}
											{$message->getUserProfile()->username}
										{/if}
									{/if}
									
									{if $message->getTime()}
										<small>{if $message->getUserProfile()}- {/if}{@$message->getTime()|time}</small>
									{/if}
									
									{if $message->getContainerTitle()}
										<small>{if $message->getUserProfile() || $message->getTime()}- {/if}<a href="{$message->getContainerLink()}">{$message->getContainerTitle()}</a></small>
									{/if}
								</p>
							{/if}
							<small class="containerContentType">{lang}wcf.search.object.{@$message->getObjectTypeName()}{/lang}</small>
						</div>
						
						<p>{@$message->getFormattedMessage()}</p>
					</div>
				</div>
			</li>
		{/foreach}
	</ul>
</div>