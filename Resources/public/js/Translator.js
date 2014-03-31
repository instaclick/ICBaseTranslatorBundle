/*global define */
define(
    'ICBaseTranslator/Translator',
    [
        'jquery',
        'Translator'
    ],
    function ($, Translator) {
        'use strict';

        var ndeLocale = null;

        $.extend(Translator, {
            getTranslationFromUrl: function (url) {
                $.ajax({
                    async: false,
                    type: 'GET',
                    url: url,
                    success: $.proxy(this.fromJSON, this),
                    dataType: 'json',
                    cache: true
                });
            },
            setLocale: function (locale) {
                ndeLocale = locale;
            },
            loadByRoute: function (route) {
                this.getTranslationFromUrl('/translations/' + route + '.json?locales=' + ndeLocale);
            }
        });

        return Translator;
    }
);
