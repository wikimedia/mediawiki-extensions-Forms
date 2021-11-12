/** the "var def = " part is here only so this is valid JS, should be removed when
 * pasting to .form wikipage.
 */
var def = {
	title: "New customer",
	showTitle: true,
	target: {
		type: 'json-on-wikipage',
		title: 'Customer_{{customerName}}'/*,
		afterAction: {
			type: 'callback',
			callback: function( result ) {
				// result.title - Name of the created page
			}
		}*/
	},
	items: [
		{
			required: true,
			name: "customerName",
			label: "Name of the company",
			type: "text"
		},
		{
			name: "noOfEmployees",
			label: "Number of employees",
			type: "number",
			min: 1,
			max: 5000,
			value: 10
		},
		{
			name: "city",
			label: "City",
			type: "text"
		},
		{
			type: 'label',
			widget_label: 'Contacts'
		},
		{
			type: 'layout_horizontal',
			showOn: [ 'create', 'edit' ],
			items: [
				{
					type: 'label',
					widget_label: 'Show contacts'
				},
				{
					name: 'showContacts',
					type: 'checkbox',
					selected: true,
					listeners: {
						change: function( newVal ) {
							if ( newVal ) {
								this.getItem( 'contactsLayout' ).$element.show();
							} else {
								this.getItem( 'contactsLayout' ).$element.hide();
							}
						}
					}
				},
				{
					type: 'label',
					widget_label: 'Show addresses'
				},
				{
					name: 'showAddresses',
					type: 'checkbox',
					selected: true,
					listeners: {
						change: function( newVal ) {
							if ( newVal ) {
								this.getItem( 'addressesLayout' ).$element.show();
							} else {
								this.getItem( 'addressesLayout' ).$element.hide();
							}
						}
					}
				}

			]
		},
		{
			name: 'contactsLayout',
			type: 'layout_booklet',
			outlined: true,
			framed: true,
			pages: [
				{
					name: 'contactSales',
					label: 'Sales',
					icon: 'ongoingConversation',
					items: [
						{
							name: "contactSalesName",
							label: "Name",
							type: "text"
						},
						{
							name: "contactSalesTel",
							label: "Phone",
							type: "text"
						},
						{
							name: "contactSalesEmail",
							icon: 'message',
							label: "Email",
							type: "text"
						}
					]
				},
				{
					name: 'contactProduction',
					label: 'production',
					icon: 'ongoingConversation',
					items: [
						{
							name: "contactProductionName",
							label: "Name",
							type: "text"
						},
						{
							name: "contactProductionTel",
							label: "Phone",
							type: "text"
						},
						{
							name: "contactProductionEmail",
							icon: 'message',
							label: "Email",
							type: "text"
						}
					]
				},
			]
		},
		{
			type: 'layout_index',
			name: 'addressesLayout',
			expanded: false,
			framed: true,
			tabs: [
				{
					name: 'addressHQ',
					label: 'Headquarters',
					items: [
						{
							name: "addressHQStreet",
							label: "Street",
							type: "text"
						},
						{
							name: "addressHQBilling",
							label: "Is billing address?",
							type: "checkbox",
							selected: true
						}
					]
				},
				{
					name: 'facilitiesTab',
					label: 'Facilities',
					items: [
						{
							name: "facilities",
							type: "checkbox-multiselect",
							label: "Facilities",
							value: [ 'production', 'sales' ],
							options: [
								{
									data: 'management',
									label: 'Management'
								}, {
									data: 'production',
									label: 'Production'
								}, {
									data: 'sales',
									label: 'Sales'
								}, {
									data: 'warehouse',
									lable: 'Warehouse'
								}
							]
						}
					]
				},
				{
					name: 'facilitiesTabRadio',
					label: 'Facilities radio',
					items: [
						{
							name: "facilities-radio",
							type: "radio-multiselect",
							label: "Facilities radio",
							value: 'sales',
							options: [
								{
									data: 'management',
									label: 'Management'
								}, {
									data: 'production',
									label: 'Production'
								}, {
									data: 'sales',
									label: 'Sales'
								}, {
									data: 'warehouse',
									label: 'Warehouse'
								}
							]
						}
					]
				},
			]
		}
	],
	listeners: {
		submit: function() {
			// Do stuff on submit - scope: form
			// Called after validation pass
		},
		reset: function() {
			// Do stuff on reset - scope: form
		},
		cancel: function() {
			// Do stuff on reset - scope: form
		}
	}
}
