if( registerPaymentMethod == undefined ) 
{
    const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
}

const eth_methods_data = window.wc.wcSettings.getSetting("eth_methods_data");

const eth_methods_label = wp.htmlEntities.decodeEntities(eth_methods_data.title || "");

const eth_methods_icon = () => {
    return eth_methods_data.icon ? React.createElement("img", {
        src: eth_methods_data.icon,
        style: {
            marginRight: '5px',
            width: '24px',
            height: '24px',
        }
    }) : null;
};

const eth_methods_Content = () => {
	return wp.htmlEntities.decodeEntities(eth_methods_data.description || "");
}

const eth_methods_Label = () => {

    return React.createElement(
        "span",
        { style: { width: '100%', display: 'flex', alignItems: 'center' } },
        React.createElement(eth_methods_icon),
        React.createElement('span', {}, eth_methods_label)
    );

}

registerPaymentMethod({
	name: "eth_methods",
	title: React.createElement(eth_methods_Label),
	description: React.createElement(eth_methods_Content),
	label: React.createElement(eth_methods_Label),
	content: React.createElement(eth_methods_Content),
	gatewayId : "eth_methods",
	edit: React.createElement(eth_methods_Content),
	canMakePayment: () => true,
	ariaLabel: eth_methods_label,
	supports: {
		features: eth_methods_data.supports,
	}
});