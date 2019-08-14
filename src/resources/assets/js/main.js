biigle.$viewModel('quotes-container', function (element) {
    var messages = biigle.$require('messages.store');
    new Vue({
        el: element,
        data: {
            quote: '',
        },
        methods: {
            refreshQuote: function () {
                this.$http.get('quotes/new')
                    .then(this.handleResponse, messages.handleErrorResponse);
            },
            handleResponse: function (response) {
                this.quote = response.body;
            },
        },
        created: function () {
            this.refreshQuote();
        },
    });
});
