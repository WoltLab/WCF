<svg class="styleOptionPreview" viewBox="0 0 160 64" aria-hidden="true">
	<rect class="styleOptionPreview__page" x="0" y="0" width="160" height="64"/>
	<rect class="styleOptionPreview__line" x="12" y="4" width="136" height="4"/>
	<rect class="styleOptionPreview__line" x="12" y="12" width="120" height="4"/>
	{if $styleOptionValue === 'classic'}
		<rect class="styleOptionPreview__facade" x="1" y="22" width="158" height="14"/>
		<rect class="styleOptionPreview__line" x="12" y="27" width="60" height="4"/>
		<rect class="styleOptionPreview__line" x="88" y="27" width="60" height="4"/>
		<rect class="styleOptionPreview__bar" x="1" y="36" width="158" height="27"/>
		<rect class="styleOptionPreview__item" x="12" y="42" width="14" height="4"/>
		<rect class="styleOptionPreview__item" x="30" y="42" width="14" height="4"/>
		<rect class="styleOptionPreview__item" x="48" y="42" width="14" height="4"/>
		<rect class="styleOptionPreview__item" x="66" y="54" width="28" height="4"/>
	{elseif $styleOptionValue === 'compact'}
		<rect class="styleOptionPreview__facade" x="12" y="20" width="67" height="11"/>
		<rect class="styleOptionPreview__facade" x="81" y="20" width="67" height="11"/>
		<rect class="styleOptionPreview__facade" x="1" y="36" width="158" height="27"/>
		<rect class="styleOptionPreview__line" x="12" y="42" width="14" height="4"/>
		<rect class="styleOptionPreview__line" x="30" y="42" width="14" height="4"/>
		<rect class="styleOptionPreview__line" x="48" y="42" width="14" height="4"/>
		<rect class="styleOptionPreview__facade" x="1" y="51" width="158" height="12"/>
		<rect class="styleOptionPreview__line" x="12" y="55" width="28" height="4"/>
		<rect class="styleOptionPreview__line" x="124" y="55" width="10" height="4"/>
		<rect class="styleOptionPreview__line" x="138" y="55" width="10" height="4"/>
	{/if}
	<rect class="styleOptionPreview__frame" x="0.5" y="0.5" width="159" height="63"/>
</svg>
