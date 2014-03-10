/*global define */
define(
    'ICBaseTranslator/Translator',
    [
        'jquery',
        'Translator'
    ],
    function ($, Translator) {
        'use strict';

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
                this.ndeLocale = locale;
            },
            loadByRoute: function (route) {
                this.getTranslationFromUrl('/i18n/' + route + '/' + this.ndeLocale + '.json');
            }
        });

        return Translator;
    }
);
