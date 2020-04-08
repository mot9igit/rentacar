rentacar.window.CreateOption = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'rentacar-option-window-create';
    }
    Ext.applyIf(config, {
        title: _('rentacar_option_create'),
        width: 550,
        autoHeight: true,
        url: rentacar.config.connector_url,
        action: 'mgr/option/create',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    rentacar.window.CreateOption.superclass.constructor.call(this, config);
};
Ext.extend(rentacar.window.CreateOption, MODx.Window, {

    getFields: function (config) {
        return [{
            layout: 'column',
            defaults: {msgTarget: 'under', border: false},
            anchor: '100%',
            items: [{
                columnWidth: .5,
                layout: 'form',
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('rentacar_option_name'),
                    name: 'name',
                    id: config.id + '-name',
                    anchor: '100%'
                }, {
                    xtype: 'numberfield',
                    fieldLabel: _('rentacar_option_price'),
                    name: 'price',
                    decimalPrecision: 2,
                    id: config.id + '-price',
                    anchor: '100%'
                }]
            }, {
                columnWidth: .5,
                layout: 'form',
                items: [{
                    xtype: 'combo-type',
                    fieldLabel: _('rentacar_option_type'),
                    name: 'type',
                    id: config.id + '-type',
                    anchor: '100%'
                }, {
                    xtype: 'numberfield',
                    name: 'free_count',
                    fieldLabel: _('rentacar_option_free_count'),
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
                    fieldLabel: _('rentacar_option_description'),
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
                    boxLabel: _('rentacar_option_checked'),
                    description: _('rentacar_option_checked'),
                    name: 'checked',
                    id: config.id + '-checked',
                    anchor: '100%'
                }]
            },{
                columnWidth: .33,
                layout: 'form',
                items: [{
                    xtype: 'xcheckbox',
                    boxLabel: _('rentacar_option_active'),
                    description: _('rentacar_option_active'),
                    name: 'active',
                    id: config.id + '-active',
                    anchor: '100%'
                }]
            },{
                columnWidth: .33,
                layout: 'form',
                items: [{
                    xtype: 'xcheckbox',
                    boxLabel: _('rentacar_option_price_perday'),
                    description: _('rentacar_option_price_perday'),
                    name: 'price_perday',
                    id: config.id + '-price_perday',
                    anchor: '100%'
                }]
            }]
        }]
    }
});
Ext.reg('rentacar-option-window-create', rentacar.window.CreateOption);


rentacar.window.UpdateOption = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'rentacar-option-window-update';
    }
    Ext.applyIf(config, {
        title: _('rentacar_option_update'),
        width: 550,
        autoHeight: true,
        url: rentacar.config.connector_url,
        action: 'mgr/option/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    rentacar.window.UpdateOption.superclass.constructor.call(this, config);
};
Ext.extend(rentacar.window.UpdateOption, MODx.Window, {

    getFields: function (config) {
        return [{xtype: 'hidden', name: 'id', id: config.id + '-id'},{
            layout: 'column',
            defaults: {msgTarget: 'under', border: false},
            anchor: '100%',
            items: [{
                columnWidth: .5,
                layout: 'form',
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('rentacar_option_name'),
                    name: 'name',
                    id: config.id + '-name',
                    anchor: '100%'
                }, {
                    xtype: 'numberfield',
                    fieldLabel: _('rentacar_option_price'),
                    name: 'price',
                    decimalPrecision: 2,
                    id: config.id + '-price',
                    anchor: '100%'
                }]
            }, {
                columnWidth: .5,
                layout: 'form',
                items: [{
                    xtype: 'combo-type',
                    fieldLabel: _('rentacar_option_type'),
                    name: 'type',
                    id: config.id + '-type',
                    anchor: '100%'
                }, {
                    xtype: 'numberfield',
                    name: 'free_count',
                    fieldLabel: _('rentacar_option_free_count'),
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
                    fieldLabel: _('rentacar_option_description'),
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
                    boxLabel: _('rentacar_option_checked'),
                    description: _('rentacar_option_checked'),
                    name: 'checked',
                    id: config.id + '-checked',
                    anchor: '100%'
                }]
            },{
                columnWidth: .33,
                layout: 'form',
                items: [{
                    xtype: 'xcheckbox',
                    boxLabel: _('rentacar_option_active'),
                    description: _('rentacar_option_active'),
                    name: 'active',
                    id: config.id + '-active',
                    anchor: '100%'
                }]
            },{
                columnWidth: .33,
                layout: 'form',
                items: [{
                    xtype: 'xcheckbox',
                    boxLabel: _('rentacar_option_price_perday'),
                    description: _('rentacar_option_price_perday'),
                    name: 'price_perday',
                    id: config.id + '-price_perday',
                    anchor: '100%'
                }]
            }]
        }]
    }

});
Ext.reg('rentacar-option-window-update', rentacar.window.UpdateOption);