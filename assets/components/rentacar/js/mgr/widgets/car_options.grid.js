rentacar.grid.Options = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'rentacar-grid-options';
    }
    Ext.applyIf(config, {
        url: rentacar.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/option/getlist',
            sort: 'id',
            dir: 'desc'
        },
        listeners: {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateOption(grid, e, row);
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
    rentacar.grid.Options.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(rentacar.grid.Options, MODx.grid.Grid, {
    windows: {},

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i>&nbsp;' + _('rentacar_option_create'),
            handler: this.createOption,
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
        return ['id', 'name', 'price', 'free_count', 'price_perday', 'type', 'checked','description', 'active', 'actions'];
    },

    getColumns: function () {
        return [{
            header: _('rentacar_option_id'),
            dataIndex: 'id',
            sortable: true,
            width: 70
        }, {
            header: _('rentacar_option_name'),
            dataIndex: 'name',
            sortable: true,
            width: 200,
        }, {
            header: _('rentacar_option_price'),
            dataIndex: 'price',
            //xtype: 'numberfield',
            decimalPrecision: 2,
            sortable: true,
            width: 200,
        }, {
            header: _('rentacar_option_type'),
            dataIndex: 'type',
            renderer: rentacar.utils.renderOptionType,
            sortable: true,
            width: 200,
        }, {
            header: _('rentacar_option_free_count'),
            dataIndex: 'free_count',
            //xtype: 'numberfield',
            sortable: true,
            width: 200,
        }, {
            header: _('rentacar_option_price_perday'),
            dataIndex: 'price_perday',
            renderer: rentacar.utils.renderBoolean,
            sortable: true,
            width: 100,
        }, {
            header: _('rentacar_option_checked'),
            dataIndex: 'checked',
            renderer: rentacar.utils.renderBoolean,
            sortable: true,
            width: 100,
        }, {
            header: _('rentacar_option_description'),
            dataIndex: 'description',
            sortable: false,
            width: 250,
        }, {
            header: _('rentacar_option_active'),
            dataIndex: 'active',
            renderer: rentacar.utils.renderBoolean,
            sortable: true,
            width: 100,
        }, {
            header: _('rentacar_option_actions'),
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

    createOption: function (btn, e) {
        var w = MODx.load({
            xtype: 'rentacar-option-window-create',
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

    updateOption: function (btn, e, row) {
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
                action: 'mgr/option/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'rentacar-option-window-update',
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

    removeOption: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('rentacar_options_remove')
                : _('rentacar_option_remove'),
            text: ids.length > 1
                ? _('rentacar_options_remove_confirm')
                : _('rentacar_option_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/option/remove',
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

    disableOption: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/option/disable',
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

    enableOption: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/option/enable',
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
Ext.reg('rentacar-grid-options', rentacar.grid.Options);
