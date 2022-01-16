{if !$groupedObjectTypes|isset && $conditions|isset}{assign var='groupedObjectTypes' value=$conditions}{/if}

<div class="section tabMenuContainer">
	<nav class="tabMenu">
		<ul>
			{foreach from=$groupedObjectTypes key='conditionGroup' item='conditionObjectTypes'}
				<li><a href="#user_{$conditionGroup|rawurlencode}">{lang}wcf.user.condition.conditionGroup.{$conditionGroup}{/lang}</a></li>
			{/foreach}
		</ul>
	</nav>
	
	{foreach from=$groupedObjectTypes key='conditionGroup' item='conditionObjectTypes'}
		<div id="user_{$conditionGroup}" class="tabMenuContent">
			{if $conditionGroup != 'userOptions'}
				<section class="section">
					<h2 class="sectionTitle">{lang}wcf.user.condition.conditionGroup.{$conditionGroup}{/lang}</h2>
			{/if}
			
			{foreach from=$conditionObjectTypes item='condition'}
				{@$condition->getProcessor()->getHtml()}
			{/foreach}
			
			{if $conditionGroup != 'userOptions'}
				</section>
			{/if}
		</div>
	{/foreach}
</div>
