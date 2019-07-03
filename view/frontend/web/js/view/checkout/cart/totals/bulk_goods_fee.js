define(
    [
        'ko',
        'MageSuite_BulkGoods/js/view/checkout/summary/bulk_goods_fee',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function (ko, Component, quote, priceUtils, totals) {
        'use strict';

        var bulk_goods_title = window.checkoutConfig.bulk_goods_title;
        var bulk_goods_fee = window.checkoutConfig.bulk_goods_fee;

        return Component.extend({

            totals: quote.getTotals(),

            getFormattedPrice: ko.observable(priceUtils.formatPrice(bulk_goods_fee, quote.getPriceFormat())),
            getBulkGoodsTitle: ko.observable(bulk_goods_title),

            isDisplayed: function () {
                return this.getValue() != 0;
            },
            getValue: function() {
                var price = 0;

                if (this.totals() && totals.getSegment('bulk_goods_fee')) {
                    price = totals.getSegment('bulk_goods_fee').value;
                }

                return price;
            }
        });
    }
);
