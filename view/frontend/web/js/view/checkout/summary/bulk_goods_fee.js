define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, quote, priceUtils, totals) {
        "use strict";

        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'MageSuite_BulkGoods/checkout/summary/bulk_goods_fee'
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,

            isDisplayed: function() {
                return this.isFullMode() && this.getPureValue() !== 0;
            },

            getValue: function() {
                var price = 0;
                if (this.totals() && totals.getSegment('bulk_goods_fee')) {
                    price = totals.getSegment('bulk_goods_fee').value;
                }
                return this.getFormattedPrice(price);
            },

            getPureValue: function() {
                var price = 0;
                if (this.totals() && totals.getSegment('bulk_goods_fee')) {
                    price = totals.getSegment('bulk_goods_fee').value;
                }
                return price;
            },

            getTitle: function() {
                return window.checkoutConfig.bulk_goods_title;
            }
        });
    }
);
