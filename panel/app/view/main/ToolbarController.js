/* global Ext, djem */
Ext.define('djem.view.main.ToolbarController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.main-toolbar',

    getButtons: function () {
        var buttons = [];
        var refs = this.getReferences();
        for (var ref in refs) {
            var obj = refs[ref];
            if (obj instanceof Ext.button.Button) {
                buttons.push({ ref: ref, obj: obj });
            }
        }
        return buttons;
    },

    init: function () {
        var me = this;
        var view = me.getView();
        view.on('change.toolbar', function (_this, name) {
            var toolbars = {
                'main': [ 'add' ],
                'content': [ 'save', 'close' ]
            };
            var user = me.lookupReference('user');
            while (user.nextSibling()) {
                user.nextSibling().destroy();
            }
            Ext.each(me.getButtons(), function (data) {
                data.obj[toolbars[name].indexOf(data.ref) < 0 ? 'hide' : 'show']();
            });
            var buttons = {};
            djem.app.fireEvent('show.toolbar', buttons);
            Ext.each(buttons.value || [], function (button) {
                view.add(button);
            });
        }).on('update.toolbar', function (ref, data) {
            var button = me.getReferences()[ref];
            if (button) {
                var actions = {
                    'enable': function () { button.enable(); },
                    'disable': function () { button.disable(); },
                    'hide': function () { button.hide(); },
                    'show': function () { button.show(); },
                    'replace': function (data) {
                        button.setGlyph(data.glyph);
                        button.setText(data.text || '?');
                        button.setParams(data);
                        if (data.menu !== undefined) { button.setMenu(data.menu); }
                    }
                };
                (actions[data.action] || function () {})(data);
            }
        });

        Ext.each(me.getButtons(), function (data) {
            data.obj.on('click', function () {
                djem.app.fireEvent('click.toolbar', data.ref, data.obj.params);
            });
        });
    }
});
