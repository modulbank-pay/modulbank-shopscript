(function () {
    "use strict";
    var modulbank = {
        customPayments: null,
        form: null,
        init: function () {
            this.customPayments = $(':input[name$="\[pm_checkbox\]"]');
            this.form = this.customPayments.parents('form:first');


            this.bind();
        },

        /**
         *
         * @param event
         * @param HTMLInputElement element
         */
        changeCustomPayments: function (event, element) {
            var fast = !event.originalEvent;
            var fields = [
                this.form.find(':input[name*="\[show_payment_methods\]"]:first').parents('div.field:first'),
            ];
            if (element.checked) {
                this.show(fields, fast);
            } else {
                this.hide(fields, fast);
            }
        },

        show: function (elements, fast) {
            for (var i = 0; i < elements.length; i++) {
                if (elements[i]) {
                    if (fast) {
                        elements[i].show();
                    } else {
                        elements[i].slideDown();
                    }
                }
            }

        },
        hide: function (elements, fast) {
            for (var i = 0; i < elements.length; i++) {
                if (elements[i]) {
                    if (fast) {
                        elements[i].hide();
                    } else {
                        elements[i].slideUp();
                    }
                }
            }
        },
        bind: function () {
            var self = this;

            this.customPayments.unbind('change').bind('change', function (event) {
                self.changeCustomPayments(event, this);
            }).trigger('change');
        }
    };
    modulbank.init();
})();
