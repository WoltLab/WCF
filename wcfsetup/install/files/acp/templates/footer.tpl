				</div>
			</div>
		</section>
	</div>
	
	{include file='pageFooter'}
</div>

{if $__isRescueMode|empty}{include file='pageMenuMobile'}{/if}

{if !$__wscMissingOwnerGroup|empty}
	<div id="wscMissingOwnerGroup" role="alert">{lang}wcf.acp.group.missingOwnerGroup{/lang}</div>
{/if}

{event name='footer'}

<!-- JAVASCRIPT_RELOCATE_POSITION -->

<a id="bottom"></a>

</body>
</html>
