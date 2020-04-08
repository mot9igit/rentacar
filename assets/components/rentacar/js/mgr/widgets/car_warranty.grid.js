rentacar.grid.Warrantys = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'rentacar-grid-warrantys';
    }
    Ext.applyIf(config, {
        url: rentacar.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/warranty/getlist',
            sort: 'id',
            dir: 'desc'
        },
        listeners: {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateWarranty(grid, e, row);
            }
        },
        viewConfig: {
            forceFit: true,
            enableRowBody: true,
            autoFill: true,
            showPreview: true,
            scrollOffset: 0,
            getRowClass: function (rec) {
                return !rec.data.active
                    ? 'rentacar-grid-row-disabled'
                    : '';
            }
        },
        paging: true,
        remoteSort: true,
        autoHeight: true,
    });
    rentacar.grid.Warrantys.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(rentacar.grid.Warrantys, MODx.grid.Grid, {
    windows: {},

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i>&nbsp;' + _('rentacar_warranty_create'),
            handler: this.createWarranty,
            scope: this
        }, '->', {
            xtype: 'rentacar-field-search',
            width: 250,
            listeners: {
                search: {
                    fn: function (field) {
                        this._doSearch(field);
                    }, scope: this
                },
                clear: {
                    fn: function (field) {
                        field.setValue('');
                        this._clearSearch();
                    }, scope: this
                },
            }
        }];
    },

    getFields: function () {
        return ['id', 'name', 'price', 'price_perday', 'checked','description', 'active', 'actions'];
    },

    getColumns: function () {
        return [{
            header: _('rentacar_warranty_id'),
            dataIndex: 'id',
            sortable: true,
            width: 70
        }, {
            header: _('rentacar_warranty_name'),
            dataIndex: 'name',
            sortable: true,
            width: 200,
        }, {
            header: _('rentacar_warranty_price'),
            dataIndex: 'price',
            //xtype: 'numberfield',
            decimalPrecision: 2,
            sortable: true,
            width: 200,
        }, {
            header: _('rentacar_warranty_price_perday'),
            dataIndex: 'price_perday',
            renderer: rentacar.utils.renderBoolean,
            sortable: true,
            width: 100,
        }, {
            header: _('rentacar_warranty_checked'),
            dataIndex: 'checked',
            renderer: rentacar.utils.renderBoolean,
            sortable: true,
            width: 100,
        }, {
            header: _('rentacar_warranty_description'),
            dataIndex: 'description',
            sortable: false,
            width: 250,
        }, {
            header: _('rentacar_warranty_active'),
            dataIndex: 'active',
            renderer: rentacar.utils.renderBoolean,
            sortable: true,
            width: 100,
        }, {
            header: _('rentacar_warranty_actions'),
            dataIndex: 'actions',
            renderer: rentacar.utils.renderActions,
            sortable: false,
            width: 100,
            id: 'actions'
        }];
    },

    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = rentacar.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },

    createWarranty: function (btn, e) {
        var w = MODx.load({
            xtype: 'rentacar-warranty-window-create',
            id: Ext.id(),
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        w.reset();
        w.setValues({active: true});
        w.show(e.target);
    },

    updateWarranty: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }
        else if (!this.menu.record) {
            return false;
        }
        var id = this.menu.record.id;

        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/warranty/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'rentacar-warranty-window-update',
                            id: Ext.id(),
                            record: r,
                            listeners: {
                                success: {
                                    fn: function () {
                                        this.refresh();
                                    }, scope: this
                                }
                            }
                        });
                        w.reset();
                        w.setValues(r.object);
                        w.show(e.target);
                    }, scope: this
                }
            }
        });
    },

    removeWarranty: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('rentacar_warrantys_remove')
                : _('rentacar_warranty_remove'),
            text: ids.length > 1
                ? _('rentacar_warrantys_remove_confirm')
                : _('rentacar_warranty_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/warranty/remove',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        return true;
    },

    disableWarranty: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/warranty/disable',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        })
    },

    enableWarranty: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/warranty/enable',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        })
    },
    onClick: function (e) {
        var elem = e.getTarget();
        if (elem.nodeName == 'BUTTON') {
            var row = this.getSelectionModel().getSelected();
            if (typeof(row) != 'undefined') {
                var action = elem.getAttribute('action');
                if (action == 'showMenu') {
                    var ri = this.getStore().find('id', row.id);
                    return this._showMenu(this, ri, e);
                }
                else if (typeof this[action] === 'function') {
                    this.menu.record = row.data;
                    return this[action](this, e);
                }
            }
        }
        return this.processEvent('click', e);
    },

    _getSelectedIds: function () {
        var ids = [];
        var selected = this.getSelectionModel().getSelections();

        for (var i in selected) {
            if (!selected.hasOwnProperty(i)) {
                continue;
            }
            ids.push(selected[i]['id']);
        }

        return ids;
    },

    _doSearch: function (tf) {
        this.getStore().baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
    },

    _clearSearch: function () {
        this.getStore().baseParams.query = '';
        this.getBottomToolbar().changePage(1);
    },
});
Ext.reg('rentacar-grid-warrantys', rentacar.grid.Warrantys);
