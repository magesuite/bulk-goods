define(
    [
        'MageSuite_BulkGoods/js/view/checkout/summary/bulk_goods_fee'
    ],
    function (Component) {
        'use strict';
        return Component.extend({
            /**
             * @override
             */
            isDisplayed: function () {
                return this.getPureValue() !== 0;
            }
        });
    }
);
