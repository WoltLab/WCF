require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/ValueInterval'], ({ ValueInterval }) => {
    // dependency '{@$dependency->getId()}'
    new ValueInterval(
        '{@$dependency->getDependentNode()->getPrefixedId()}Container',
        '{@$dependency->getField()->getPrefixedId()}'
    )
    .minimum({if $dependency->getMinimum() !== null}{@$dependency->getMinimum()}{else}null{/if})
    .maximum({if $dependency->getMaximum() !== null}{@$dependency->getMaximum()}{else}null{/if});
});
