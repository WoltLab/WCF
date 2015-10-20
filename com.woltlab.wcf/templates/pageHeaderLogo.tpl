<div id="logo" class="logo">
	{if MODULE_WCF_AD && $__disableAds|empty}{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.logo')}{/if}
	
	<a href="{link}{/link}">
		{* @TODO *}
		<img src="http://192.168.0.102/w/275/wcf/images/wbb.png" alt="" class="large">
		<img src="http://192.168.0.102/w/275/wcf/images/wbb-small.png" alt="" class="small">
		{if $__wcf->getStyleHandler()->getStyle()->getPageLogo()}
			<img src="{$__wcf->getStyleHandler()->getStyle()->getPageLogo()}" alt="">
		{/if}
		{event name='headerLogo'}
	</a>
</div>