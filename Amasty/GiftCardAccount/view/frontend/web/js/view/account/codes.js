/**
 * Account gift codes
 */
define([
    'underscore',
    'uiCollection',
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/modal/confirm',
    'mage/translate',
    'Amasty_GiftCardAccount/js/model/account/codes/messages',
    'Amasty_GiftCardAccount/js/action/account-gift-code-actions'
], function (_, uiCollection, $, registry, confirm, $t, messageContainer, giftCodeActions) {
    'use strict';

    return uiCollection.extend({
        defaults: {
            deleteCardUrl: '',
            isAccount: '',
            confirmMsg: $t('Are you sure you want to remove?'),
            links: {
                cards: '${ "amcard-giftcards" }:cards'
            }
        },

        initObservable: function () {
            this._super()
                .observe(['cards']);

            return this;
        },

        hasCards: function () {
            return _.isArray(this.cards()) && this.cards().length > 0;
        },

        removeGiftCode: function (id) {
            let url = this.deleteCardUrl + 'account_id/' + id,
                giftcardProvider = registry.get('amcard-giftcards'),
                self = this;

            confirm({
                content: self.confirmMsg,
                actions: {
                    confirm: function () {
                        giftCodeActions.remove(url).done(function (response) {
                            if (!response.error) {
                                giftcardProvider.removeCard(id);
                                messageContainer.addSuccessMessage({message: response.message});

                                return;
                            }

                            messageContainer.addErrorMessage({message: response.message});
                        }.bind(this));
                    }
                }
            });
        }
    });
});
