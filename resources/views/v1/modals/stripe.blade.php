@php
    /**
     * Comment: A pop-up to collect user charge card info and send to stripe
     * Resurrected on: 11/19/2017
     */
@endphp

<div class="modal fade" id="stripe_modal" tabindex="-1" role="dialog" aria-labelledby="stripe_label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stripe_label">mCentric @lang('messages.headers.form')</h5>
                <button type="button" class="close pull-right" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                {{ html()->form('POST', env('APP_URL') . '/nomatter/' . $rf->regID)->id('payment-form')->open() }}
                <div class="form-row" class="form-group">
                    <label for="card-element">
                        @lang('messages.fields.c_or_d')
                    </label>
                    <div class="form-group" id="card-element">
                        <!-- a Stripe Element will be inserted here. -->
                    </div>
                    <!-- Used to display form errors -->
                    <div id="card-errors" class="form-group" role="alert"></div>
                </div>

                <input type="submit" class="submit form-control btn btn-primary"
                       value="{{ trans('messages.headers.pay') }} ${{ $amt }}">
                {{ html()->form()->close() }}

            </div>
            <div class="modal-footer">
                <div class="container">
                    <div class="col-sm-10" style="text-align: left;">
                        @lang('messages.messages.credit_info')
                    </div>
                    <div class="col-sm-1">
                        <button type="button" class="btn btn-secondary btn-sm"
                                data-dismiss="modal">@lang('messages.buttons.close')</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ $cspScriptNonce }}">
    var stripe = Stripe('{{ env('STRIPE_KEY') }}');
    var elements = stripe.elements();

    // Add an instance of the card UI component into the `card-element` <div>
    var card = elements.create('card');
    card.mount('#card-element');

    card.addEventListener('change', function (event) {
        var displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });

    // Create a token or display an error when the form is submitted.
    var form = document.getElementById('payment-form');
    form.addEventListener('submit', function (event) {
        event.preventDefault();

        stripe.createToken(card).then(function (result) {
            if (result.error) {
                // Inform the customer that there was an error.
                var errorElement = document.getElementById('card-errors');
                errorElement.textContent = result.error.message;
            } else {
                // Send the token to your server.
                stripeTokenHandler(result.token);
            }
        });
    });

    function stripeTokenHandler(token) {
        // Insert the token ID into the form so it gets submitted to the server
        // var form = document.getElementById('payment-form');
        var form = document.getElementById('complete_registration');
        var hiddenInput1 = document.createElement('input');
        var hiddenInput2 = document.createElement('input');
        hiddenInput1.setAttribute('type', 'hidden');
        hiddenInput1.setAttribute('name', 'stripeToken');
        hiddenInput1.setAttribute('value', token.id);
        form.appendChild(hiddenInput1);

        hiddenInput2.setAttribute('type', 'hidden');
        hiddenInput2.setAttribute('name', 'stripeTokenType');
        hiddenInput2.setAttribute('value', token.type);
        form.appendChild(hiddenInput2);

        // Submit the form
        form.submit();
    }

</script>
