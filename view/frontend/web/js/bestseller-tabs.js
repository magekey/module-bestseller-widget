/**
 * Copyright © MageKey. All rights reserved.
 */
define([
    'jquery'
], function ($) {
    "use strict";

    $.widget('mage.mgkBestsellerTabs', {

        options: {
            linkSelector: "[data-role=tab-link]",
            linkIdAttribute: "href",
            contentSelector: "[data-role=tab-content]",
            contentIdAttribute: "data-tab-id",
            activeClass: "_active"
        },

        _create: function () {
            this.initTabs();
        },

        initTabs: function () {
            var self = this,
                links;
            links = self.element.find(self.options.linkSelector);
            links.each(function (i, item) {
                    $(item).click(function () {
                        if ($(item).hasClass(self.options.activeClass)) {
                            return false;
                        }
                        self.element
                            .find(self.options.linkSelector)
                            .filter('.' + self.options.activeClass)
                            .removeClass(self.options.activeClass);
                        self.element
                            .find(self.options.contentSelector)
                            .filter('.' + self.options.activeClass)
                            .removeClass(self.options.activeClass);

                        $(item).addClass(self.options.activeClass);
                        var tabId = $(this).attr(self.options.linkIdAttribute).replace('#', '');
                        self.element
                            .find('[' + self.options.contentIdAttribute + '=' + tabId + ']')
                            .addClass(self.options.activeClass);
                        return false;
                    });
                });
            links.first().click();
        }
    });

    return $.mage.mgkBestsellerTabs;
});
