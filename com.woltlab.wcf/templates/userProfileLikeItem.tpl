{foreach from=$likeList item=like}
	<li>
		<div class="box48">
			{user object=$like->getUserProfile() type='avatar48' title=$like->getUserProfile()->username ariaHidden='true'}
			
			<div>
				<div class="containerHeadline">
					<h3>
						{user object=$like->getUserProfile()}
						<small class="separatorLeft">{@$like->time|time}</small>
					</h3>
					<div>{@$like->getTitle()}</div>
					<small class="containerContentType">{$like->getObjectTypeDescription()}</small>
				</div>
				
				<div class="containerContent">{@$like->getDescription()}</div>
			</div>
		</div>
	</li>
{/foreach}
