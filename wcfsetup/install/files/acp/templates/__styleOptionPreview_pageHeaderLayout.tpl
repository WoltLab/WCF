<svg class="styleOptionPreview" viewBox="0 0 160 64" aria-hidden="true">
	<rect class="styleOptionPreview__page" x="0" y="0" width="160" height="64"/>
	{if $styleOptionValue === 'classic'}
		<rect class="styleOptionPreview__bar" x="1" y="1" width="158" height="13"/>
		<rect class="styleOptionPreview__item" x="12" y="3" width="14" height="8"/>
		<rect class="styleOptionPreview__item" x="30" y="3" width="14" height="8"/>
		<rect class="styleOptionPreview__item" x="48" y="3" width="14" height="8"/>
		<rect class="styleOptionPreview__item" x="128" y="3" width="8" height="8"/>
		<rect class="styleOptionPreview__item" x="140" y="3" width="8" height="8"/>
		<rect class="styleOptionPreview__facade" x="1" y="14" width="158" height="26"/>
		<rect class="styleOptionPreview__logo" x="12" y="22" width="44" height="10" rx="2"/>
		<rect class="styleOptionPreview__line" x="12" y="48" width="136" height="4"/>
		<rect class="styleOptionPreview__line" x="12" y="56" width="120" height="4"/>
	{elseif $styleOptionValue === 'logoTop'}
		<rect class="styleOptionPreview__facade" x="1" y="1" width="158" height="25"/>
		<rect class="styleOptionPreview__logo" x="12" y="8" width="44" height="10" rx="2"/>
		<rect class="styleOptionPreview__bar" x="1" y="26" width="158" height="14"/>
		<rect class="styleOptionPreview__item" x="12" y="31" width="14" height="4"/>
		<rect class="styleOptionPreview__item" x="30" y="31" width="14" height="4"/>
		<rect class="styleOptionPreview__item" x="48" y="31" width="14" height="4"/>
		<rect class="styleOptionPreview__item" x="136" y="31" width="4" height="4"/>
		<rect class="styleOptionPreview__item" x="144" y="31" width="4" height="4"/>
		<rect class="styleOptionPreview__line" x="12" y="48" width="136" height="4"/>
		<rect class="styleOptionPreview__line" x="12" y="56" width="120" height="4"/>
	{elseif $styleOptionValue === 'logoBelow'}
		<rect class="styleOptionPreview__bar" x="1" y="1" width="158" height="13"/>
		<rect class="styleOptionPreview__item" x="12" y="5" width="14" height="4"/>
		<rect class="styleOptionPreview__item" x="30" y="5" width="14" height="4"/>
		<rect class="styleOptionPreview__item" x="48" y="5" width="14" height="4"/>
		<rect class="styleOptionPreview__item" x="136" y="5" width="4" height="4"/>
		<rect class="styleOptionPreview__item" x="144" y="5" width="4" height="4"/>
		<rect class="styleOptionPreview__facade" x="1" y="14" width="158" height="26"/>
		<rect class="styleOptionPreview__logo" x="12" y="22" width="44" height="10" rx="2"/>
		<rect class="styleOptionPreview__line" x="12" y="48" width="136" height="4"/>
		<rect class="styleOptionPreview__line" x="12" y="56" width="120" height="4"/>
	{elseif $styleOptionValue === 'logoInBar'}
		<rect class="styleOptionPreview__bar" x="1" y="1" width="158" height="15"/>
		<rect class="styleOptionPreview__logo" x="12" y="4" width="22" height="8" rx="2"/>
		<rect class="styleOptionPreview__item" x="40" y="6" width="14" height="4"/>
		<rect class="styleOptionPreview__item" x="58" y="6" width="14" height="4"/>
		<rect class="styleOptionPreview__item" x="76" y="6" width="14" height="4"/>
		<rect class="styleOptionPreview__item" x="136" y="6" width="4" height="4"/>
		<rect class="styleOptionPreview__item" x="144" y="6" width="4" height="4"/>
		<rect class="styleOptionPreview__line" x="12" y="24" width="136" height="4"/>
		<rect class="styleOptionPreview__line" x="12" y="32" width="120" height="4"/>
		<rect class="styleOptionPreview__line" x="12" y="40" width="128" height="4"/>
		<rect class="styleOptionPreview__line" x="12" y="48" width="136" height="4"/>
		<rect class="styleOptionPreview__line" x="12" y="56" width="104" height="4"/>
	{/if}
	<rect class="styleOptionPreview__frame" x="0.5" y="0.5" width="159" height="63"/>
</svg>
