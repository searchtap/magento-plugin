define([
    'jquery'
], function ($) {
    'use strict';

    var formMiniWidgetMixin = {
        _create: function () {
            this.options.minSearchLength = 1000;
            this.options.autocomplete = "off"
            this._super();
        }
    };

    return function (targetWidget) {
        $.widget('mage.quickSearch', targetWidget, formMiniWidgetMixin);
        return $.mage.quickSearch;
    };
});