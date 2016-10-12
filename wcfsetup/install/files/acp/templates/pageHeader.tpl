<div class="pageHeaderContainer">
	<header id="pageHeader" class="pageHeader">
		<div id="pageHeaderPanel" class="pageHeaderPanel">
			<div class="layoutBoundary">
				{include file='pageHeaderLogo'}
				
				{* hide everything except the logo during login / in rescue mode *}
				{if $__isLogin|empty}
					{include file='pageHeaderUser'}
					
					{include file='pageHeaderMenu'}
					
					{include file='pageHeaderSearch'}
				{/if}
			</div>
		</div>
	</header>
</div>