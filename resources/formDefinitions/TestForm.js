/** the 'var def = ' part is here only so this is valid JS, should be removed when
 * pasting to .form wikipage.
 */
var def = {
	title: mw.message( 'forms-title-new-customer' ).text(),
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
			name: 'customerName',
			label: mw.message( 'forms-label-company-name' ).text(),
			type: 'text'
		},
		{
			name: 'noOfEmployees',
			label: mw.message( 'forms-label-employees-number' ).text(),
			type: 'number',
			min: 1,
			max: 5000,
			value: 10
		},
		{
			name: 'city',
			label: mw.message( 'forms-label-city' ).text(),
			type: 'text'
		},
		{
			type: 'label',
			widget_label: mw.message( 'forms-label-contacts' ).text()
		},
		{
			type: 'layout_horizontal',
			showOn: [ 'create', 'edit' ],
			items: [
				{
					type: 'label',
					widget_label: mw.message( 'forms-label-show-contacts' ).text()
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
					widget_label: mw.message( 'forms-label-show-address' ).text()
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
					label: mw.message( 'forms-label-sales' ).text(),
					icon: 'ongoingConversation',
					items: [
						{
							name: 'contactSalesName',
							label: mw.message( 'mwstake-formengine-label-name' ).text(),
							type: 'text'
						},
						{
							name: 'contactSalesTel',
							label: mw.message( 'forms-label-phone' ).text(),
							type: 'text'
						},
						{
							name: 'contactSalesEmail',
							icon: 'message',
							label: mw.message( 'forms-label-email' ).text(),
							type: 'text'
						}
					]
				},
				{
					name: 'contactProduction',
					label: mw.message( 'forms-label-production' ).text(),
					icon: 'ongoingConversation',
					items: [
						{
							name: 'contactProductionName',
							label: mw.message( 'mwstake-formengine-label-name' ).text(),
							type: 'text'
						},
						{
							name: 'contactProductionTel',
							label: mw.message( 'forms-label-phone' ).text(),
							type: 'text'
						},
						{
							name: 'contactProductionEmail',
							icon: 'message',
							label: mw.message( 'forms-label-email' ).text(),
							type: 'text'
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
					label: mw.message( 'forms-label-hq' ).text(),
					items: [
						{
							name: 'addressHQStreet',
							label: mw.message( 'forms-label-street' ).text(),
							type: 'text'
						},
						{
							name: 'addressHQBilling',
							label: mw.message( 'forms-label-hq-address-billing' ).text(),
							type: 'checkbox',
							selected: true
						}
					]
				},
				{
					name: 'facilitiesTab',
					label: mw.message( 'forms-label-facilities' ).text(),
					items: [
						{
							name: 'facilities',
							type: 'checkbox-multiselect',
							label: mw.message( 'forms-label-facilities' ).text(),
							value: [ 'production', 'sales' ],
							options: [
								{
									data: 'management',
									label: mw.message( 'forms-label-management' ).text()
								}, {
									data: 'production',
									label: mw.message( 'forms-label-production' ).text()
								}, {
									data: 'sales',
									label: mw.message( 'forms-label-sales' ).text()
								}, {
									data: 'warehouse',
									lable: mw.message( 'forms-label-warehouse' ).text()
								}
							]
						}
					]
				},
				{
					name: 'facilitiesTabRadio',
					label: mw.message( 'forms-label-facilities-radio' ).text(),
					items: [
						{
							name: 'facilities-radio',
							type: 'radio-multiselect',
							label: mw.message( 'forms-label-facilities-radio' ).text(),
							value: 'sales',
							options: [
								{
									data: 'management',
									label: mw.message( 'forms-label-management' ).text()
								}, {
									data: 'production',
									label: mw.message( 'forms-label-production' ).text()
								}, {
									data: 'sales',
									label: mw.message( 'forms-label-sales' ).text()
								}, {
									data: 'warehouse',
									label: mw.message( 'forms-label-warehouse' ).text()
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
