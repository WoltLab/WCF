{if !$groupedObjectTypes|isset && $conditions|isset}{assign var='groupedObjectTypes' value=$conditions}{/if}

<div class="tabMenuContainer">
	<nav class="tabMenu">
		<ul>
			{foreach from=$groupedObjectTypes key='conditionGroup' item='conditionObjectTypes'}
				{assign var='__anchor' value='user_'|concat:$conditionGroup}
				<li><a href="{@$__wcf->getAnchor($__anchor)}">{lang}wcf.user.condition.conditionGroup.{$conditionGroup}{/lang}</a></li>
			{/foreach}
		</ul>
	</nav>
	
	{foreach from=$groupedObjectTypes key='conditionGroup' item='conditionObjectTypes'}
		<div id="user_{$conditionGroup}" class="container containerPadding tabMenuContent">
			{if $conditionGroup != 'userOptions'}
				<fieldset>
					<legend>{lang}wcf.user.condition.conditionGroup.{$conditionGroup}{/lang}</legend>
			{/if}
			
			{foreach from=$conditionObjectTypes item='condition'}
				{@$condition->getProcessor()->getHtml()}
			{/foreach}
			
			{if $conditionGroup != 'userOptions'}
				</fieldset>
			{/if}
		</div>
	{/foreach}
</div>
