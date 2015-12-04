<{if $box->showHeader}section{else}div{/if} class="box">
	{if $box->showHeader}<h1 class="boxTitle">{$box->getTitle()}</h1>{/if}
	
	<div class="boxContent">
		{@$box->getContent()}
	</div>
{if $box->showHeader}</section>{else}</div>{/if}
