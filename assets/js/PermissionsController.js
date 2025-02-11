/*!
 * Контроллер представления виджета формы установки права доступа файлу / папки.
 * Модуль "Файловый менеджер".
 * Copyright 2015 Вeб-студия GearMagic. Anton Tivonenko <anton.tivonenko@gmail.com>
 * https://gearmagic.ru/license/
 */

Ext.define('Gm.be.filemanager.PermissionsController', {
    extend: 'Gm.view.form.PanelController',
    alias: 'controller.gm-be-filemanager-pms',

    /**
     * Устанавливает числовое значение прав доступа.
     * @param {Number} or, ow, ox, gr, gw, gx, wr, ww, wx
     */
    setPermissions: function (or, ow, ox, gr, gw, gx, wr, ww, wx) {
        Ext.getCmp(this.view.id + '__permissions').setValue('0' + String(or|ow|ox) + String(gr|gw|gx) + String(wr|ww|wx));
    },

    /**
     * Возвращает состояние флага прав доступа.
     * @param {String} name Название прав доступа.
     */
    getGroupChecked: function (name) { return Ext.getCmp(this.view.id + '__' + name).checked; },

    /**
     * Событие нажатия на флаг прав доступа.
     * @param {Ext.form.field.Checkbox} me
     * @param {Boolean} value Значение.
     */
    onCheckPermission: function (me, value) {
        let or = this.getGroupChecked('or') ? 4 : 0,
            ow = this.getGroupChecked('ow') ? 2 : 0,
            ox = this.getGroupChecked('ox') ? 1 : 0,
            gr = this.getGroupChecked('gr') ? 4 : 0,
            gw = this.getGroupChecked('gw') ? 2 : 0,
            gx = this.getGroupChecked('gx') ? 1 : 0,
            wr = this.getGroupChecked('wr') ? 4 : 0,
            ww = this.getGroupChecked('ww') ? 2 : 0,
            wx = this.getGroupChecked('wx') ? 1 : 0;
        this.setPermissions(or, ow, ox, gr, gw, gx, wr, ww, wx);
    }
});