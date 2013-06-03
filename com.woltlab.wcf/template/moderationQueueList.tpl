{foreach from=$queues item=queue}
	<li>
		<a href="{@$queue->getLink()}" class="box24">
			<div class="framed">
				{if $queue->userID}
					{@$queue->getUserProfile()->getAvatar()->getImageTag(24)}
				{else}
					<img src="{$__wcf->getPath()}images/avatars/avatar-default.svg" alt="" style="width: 24px; height: 24px;" />
				{/if}
			</div>
			<div>
				<h3>{$queue->getAffectedObject()->getTitle()}</h3>
				<small>{$queue->getAffectedObject()->getUsername()} - {@$queue->getAffectedObject()->getTime()|time}</small>
			</div>
		</a>
	</li>
{/foreach}