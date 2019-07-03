define([
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ], function (Component, quote, priceUtils, totals) {
        "use strict";

        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'MageSuite_BulkGoods/checkout/summary/bulk_goods_fee'
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
            isDisplayed: function() {
                return this.isFullMode();
            },
            getValue: function() {
                var price = 0;

                if (this.totals()) {
                    price = totals.getSegment('bulk_goods_fee').value;
                }
                return this.getFormattedPrice(price);
            },
            getBaseValue: function() {
                var price = 0;

                if (this.totals()) {
                    price = totals.getSegment('bulk_goods_fee').value;
                }
                return priceUtils.formatPrice(price, quote.getBasePriceFormat());
            }
        });
    }
);
