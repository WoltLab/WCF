<div id="logo" class="logo">
	{if MODULE_WCF_AD && $__disableAds|empty}{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.logo')}{/if}
	
	<a href="{link}{/link}">
		{* @TODO *}
		<img src="{@$__wcf->getPath()}images/default-logo.png" alt="" class="large">
		<img src="{@$__wcf->getPath()}images/default-logo-small.png" alt="" class="small">
		{*if $__wcf->getStyleHandler()->getStyle()->getPageLogo()}
			<img src="{$__wcf->getStyleHandler()->getStyle()->getPageLogo()}" alt="">
		{/if*}
		{event name='headerLogo'}
	</a>
</div>