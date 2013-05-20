{foreach from=$queues item=queue}
	<li>
		<a href="{@$queue->getLink()}" class="box24">
			<div class="framed">
				{@$queue->getUserProfile()->getAvatar()->getImageTag(24)}
			</div>
			<div>
				<h3>{$queue->getAffectedObject()->getTitle()}</h3>
				<small>{$queue->getAffectedObject()->getUsername()} - {@$queue->getAffectedObject()->getTime()|time}</small>
			</div>
		</a>
	</li>
{/foreach}