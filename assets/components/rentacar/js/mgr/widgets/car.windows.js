rentacar.window.CreateCar = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'rentacar-car-window-create';
    }
    Ext.applyIf(config, {
        title: _('rentacar_car_create'),
        width: 550,
        autoHeight: true,
        url: rentacar.config.connector_url,
        action: 'mgr/car/create',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    rentacar.window.CreateCar.superclass.constructor.call(this, config);
};
Ext.extend(rentacar.window.CreateCar, MODx.Window, {

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
                    fieldLabel: _('rentacar_car_name'),
                    name: 'name',
                    id: config.id + '-name',
                    anchor: '100%'
                }]
            }],
        }, {
            layout: 'column',
            defaults: {msgTarget: 'under', border: false},
            anchor: '100%',
            items: [{
                columnWidth: .5,
                layout: 'form',
                items: [{
                    xtype: 'modx-combo-browser',
                    fieldLabel: _('rentacar_car_photo'),
                    name: 'photo',
                    id: config.id + '-photo',
                    anchor: '100%'
                }, {
                    xtype: 'rentacar-combo-region',
                    fieldLabel: _('rentacar_car_region'),
                    name: 'region',
                    id: config.id + '-region',
                    anchor: '100%'
                }]
            }, {
                columnWidth: .5,
                layout: 'form',
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('rentacar_car_number'),
                    name: 'number',
                    id: config.id + '-number',
                    anchor: '100%'
                }, {
                    xtype: 'rentacar-combo-resource',
                    name: 'resource',
                    fieldLabel: _('rentacar_car_resource'),
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
                    fieldLabel: _('rentacar_car_description'),
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
                    boxLabel: _('rentacar_option_active'),
                    description: _('rentacar_option_active'),
                    name: 'active',
                    id: config.id + '-active',
                    anchor: '100%'
                }]
            }]
        }]
    }
});
Ext.reg('rentacar-car-window-create', rentacar.window.CreateCar);


rentacar.window.UpdateCar = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'rentacar-car-window-update';
    }
    Ext.applyIf(config, {
        title: _('rentacar_option_update'),
        width: 550,
        autoHeight: true,
        url: rentacar.config.connector_url,
        action: 'mgr/car/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    rentacar.window.UpdateCar.superclass.constructor.call(this, config);
};
Ext.extend(rentacar.window.UpdateCar, MODx.Window, {

    getFields: function (config) {
        return [
            {
                xtype: 'hidden',
                name: 'id',
                id: config.id + '-id'
            },{
                layout: 'column',
                defaults: {msgTarget: 'under', border: false},
                anchor: '100%',
                items: [{
                    columnWidth: 1,
                    layout: 'form',
                    items: [{
                        xtype: 'textfield',
                        fieldLabel: _('rentacar_car_name'),
                        name: 'name',
                        id: config.id + '-name',
                        anchor: '100%'
                    }]
                }],
            }, {
            layout: 'column',
            defaults: {msgTarget: 'under', border: false},
            anchor: '100%',
            items: [{
                columnWidth: .5,
                layout: 'form',
                items: [{
                    xtype: 'modx-combo-browser',
                    fieldLabel: _('rentacar_car_photo'),
                    name: 'photo',
                    id: config.id + '-photo',
                    anchor: '100%'
                }, {
                    xtype: 'textfield',
                    fieldLabel: _('rentacar_car_region'),
                    xtype: 'rentacar-combo-region',
                    name: 'region',
                    id: config.id + '-region',
                    anchor: '100%'
                }]
            }, {
                columnWidth: .5,
                layout: 'form',
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('rentacar_car_number'),
                    name: 'number',
                    id: config.id + '-number',
                    anchor: '100%'
                }, {
                    xtype: 'rentacar-combo-resource',
                    name: 'resource',
                    fieldLabel: _('rentacar_car_resource'),
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
                    fieldLabel: _('rentacar_car_description'),
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
                    boxLabel: _('rentacar_option_active'),
                    description: _('rentacar_option_active'),
                    name: 'active',
                    id: config.id + '-active',
                    anchor: '100%'
                }]
            }]
        }]
    }
});
Ext.reg('rentacar-car-window-update', rentacar.window.UpdateCar);