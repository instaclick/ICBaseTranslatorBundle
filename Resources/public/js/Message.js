/*global define */
define(
    'TranslatorMessage',
    [
        'Translator'
    ],
    function (Translator) {
        'use strict';

        Translator.loadByRoute('messages');
    }
);
