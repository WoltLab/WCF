{event name='copyright'}

{if (!'WOLTLAB_BRANDING'|defined || WOLTLAB_BRANDING) && (!$showWoltLabBranding|isset || $showWoltLabBranding)}<div class="copyright">{lang}wcf.page.copyright{/lang}</div>{/if}
