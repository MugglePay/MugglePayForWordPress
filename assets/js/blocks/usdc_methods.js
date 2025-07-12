if( registerPaymentMethod == undefined ) 
{
    const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
}

const usdc_methods_data = window.wc.wcSettings.getSetting("usdc_methods_data");

const usdc_methods_label = wp.htmlEntities.decodeEntities(usdc_methods_data.title || "");

const usdc_methods_icon = () => {
    return usdc_methods_data.icon ? React.createElement("img", {
        src: usdc_methods_data.icon,
        style: {
            marginRight: '5px',
            width: '24px',
            height: '24px',
        }
    }) : null;
};

const usdc_methods_Content = () => {
	return wp.htmlEntities.decodeEntities(usdc_methods_data.description || "");
}

const usdc_methods_Label = (props) => {
    return React.createElement(
        "span",
        { style: { width: '100%', display: 'flex', alignItems: 'center' } },
        React.createElement(usdc_methods_icon),
        React.createElement('span', {}, usdc_methods_label)
    );
}

registerPaymentMethod({
	name: "usdc_methods",
	title: React.createElement(usdc_methods_Label),
	description: React.createElement(usdc_methods_Content),
	label: React.createElement(usdc_methods_Label),
	content: React.createElement(usdc_methods_Content),
	gatewayId : "usdc_methods",
	edit: React.createElement(usdc_methods_Content),
	canMakePayment: () => true,
	ariaLabel: usdc_methods_label,
	supports: {
		features: usdc_methods_data.supports,
	}
});