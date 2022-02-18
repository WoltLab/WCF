require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/IsNotClicked'], ({ IsNotClicked }) => {
	// dependency '{@$dependency->getId()}'
	new IsNotClicked(
		'{@$dependency->getDependentPrefixedId()}',
		'{@$dependency->getField()->getPrefixedId()}'
	);
});
