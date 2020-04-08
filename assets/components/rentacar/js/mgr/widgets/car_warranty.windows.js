rentacar.window.CreateWarranty = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'rentacar-warranty-window-create';
    }
    Ext.applyIf(config, {
        title: _('rentacar_warranty_create'),
        width: 550,
        autoHeight: true,
        url: rentacar.config.connector_url,
        action: 'mgr/warranty/create',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    rentacar.window.CreateWarranty.superclass.constructor.call(this, config);
};
Ext.extend(rentacar.window.CreateWarranty, MODx.Window, {

    getFields: function (config) {
        return [{
            layout: 'column',
            defaults: {msgTarget: 'under', border: false},
            anchor: '100%',
            items: [{
                columnWidth: 1,
                layout: 'form',
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('rentacar_warranty_name'),
                    name: 'name',
                    id: config.id + '-name',
                    anchor: '100%'
                }, {
                    xtype: 'numberfield',
                    fieldLabel: _('rentacar_warranty_price'),
                    name: 'price',
                    decimalPrecision: 2,
                    id: config.id + '-price',
                    anchor: '100%'
                }]
            }]
        }, {
            layout: 'column',
            defaults: {msgTarget: 'under', border: false},
            anchor: '100%',
            items: [{
                columnWidth: 1,
                layout: 'form',
                items: [{
                    xtype: 'textarea',
                    fieldLabel: _('rentacar_warranty_description'),
                    name: 'description',
                    id: config.id + '-description',
                    height: 150,
                    anchor: '100%'
                }]
            }],
        }, {
            layout: 'column',
            defaults: {msgTarget: 'under', border: false},
            anchor: '100%',
            items: [{
                columnWidth: .33,
                layout: 'form',
                items: [{
                    xtype: 'xcheckbox',
                    boxLabel: _('rentacar_warranty_checked'),
                    description: _('rentacar_warranty_checked'),
                    name: 'checked',
                    id: config.id + '-checked',
                    anchor: '100%'
                }]
            },{
                columnWidth: .33,
                layout: 'form',
                items: [{
                    xtype: 'xcheckbox',
                    boxLabel: _('rentacar_warranty_active'),
                    description: _('rentacar_warranty_active'),
                    name: 'active',
                    id: config.id + '-active',
                    anchor: '100%'
                }]
            },{
                columnWidth: .33,
                layout: 'form',
                items: [{
                    xtype: 'xcheckbox',
                    boxLabel: _('rentacar_warranty_price_perday'),
                    description: _('rentacar_warranty_price_perday'),
                    name: 'price_perday',
                    id: config.id + '-price_perday',
                    anchor: '100%'
                }]
            }]
        }]
    }
});
Ext.reg('rentacar-warranty-window-create', rentacar.window.CreateWarranty);


rentacar.window.UpdateWarranty = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'rentacar-warranty-window-update';
    }
    Ext.applyIf(config, {
        title: _('rentacar_warranty_update'),
        width: 550,
        autoHeight: true,
        url: rentacar.config.connector_url,
        action: 'mgr/warranty/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    rentacar.window.UpdateWarranty.superclass.constructor.call(this, config);
};
Ext.extend(rentacar.window.UpdateWarranty, MODx.Window, {

    getFields: function (config) {
        return [{xtype: 'hidden', name: 'id', id: config.id + '-id'},{
            layout: 'column',
            defaults: {msgTarget: 'under', border: false},
            anchor: '100%',
            items: [{
                columnWidth: 1,
                layout: 'form',
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('rentacar_warranty_name'),
                    name: 'name',
                    id: config.id + '-name',
                    anchor: '100%'
                }, {
                    xtype: 'numberfield',
                    fieldLabel: _('rentacar_warranty_price'),
                    name: 'price',
                    decimalPrecision: 2,
                    id: config.id + '-price',
                    anchor: '100%'
                }]
            }]
        }, {
            layout: 'column',
            defaults: {msgTarget: 'under', border: false},
            anchor: '100%',
            items: [{
                columnWidth: 1,
                layout: 'form',
                items: [{
                    xtype: 'textarea',
                    fieldLabel: _('rentacar_warranty_description'),
                    name: 'description',
                    id: config.id + '-description',
                    height: 150,
                    anchor: '100%'
                }]
            }],
        }, {
            layout: 'column',
            defaults: {msgTarget: 'under', border: false},
            anchor: '100%',
            items: [{
                columnWidth: .33,
                layout: 'form',
                items: [{
                    xtype: 'xcheckbox',
                    boxLabel: _('rentacar_warranty_checked'),
                    description: _('rentacar_warranty_checked'),
                    name: 'checked',
                    id: config.id + '-checked',
                    anchor: '100%'
                }]
            },{
                columnWidth: .33,
                layout: 'form',
                items: [{
                    xtype: 'xcheckbox',
                    boxLabel: _('rentacar_warranty_active'),
                    description: _('rentacar_warranty_active'),
                    name: 'active',
                    id: config.id + '-active',
                    anchor: '100%'
                }]
            },{
                columnWidth: .33,
                layout: 'form',
                items: [{
                    xtype: 'xcheckbox',
                    boxLabel: _('rentacar_warranty_price_perday'),
                    description: _('rentacar_warranty_price_perday'),
                    name: 'price_perday',
                    id: config.id + '-price_perday',
                    anchor: '100%'
                }]
            }]
        }]
    }

});
Ext.reg('rentacar-warranty-window-update', rentacar.window.UpdateWarranty);