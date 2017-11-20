<?php
/**
 * Comment: A pop-up to collect user charge card info and send to stripe
 * Resurrected on: 11/19/2017
 *
 * Implementing because the Payment button only shows on certain browsers
 */
?>

<div class="modal fade" id="stripe_modal" tabindex="-1" role="dialog" aria-labelledby="stripe_label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stripe_label">mCentric Payment Form</h5>
                <button type="button" class="close pull-right" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                {{--
                <form action="/charge" method="post" id="payment-form">
                --}}
                {!! Form::open(['url' => env('APP_URL').'/nomatter/'.$rf->regID,
                        'method' => 'patch', 'id' => 'payment-form', 'data-toggle' => 'validator']) !!}
                    <div class="form-row" class="form-group">
                        <label for="card-element">
                            Credit or Debit Card
                        </label>
                        <div class="form-group" id="card-element">
                            <!-- a Stripe Element will be inserted here. -->
                        </div>
                        <!-- Used to display form errors -->
                        <div id="card-errors" class="form-group" role="alert"></div>
                    </div>

                    <input type="submit" class="submit form-control btn btn-primary" value="Pay ${{ $amt }}">
                {!! Form::close() !!}

            </div>
            <div class="modal-footer">
                <div class="container">
                    <div class="col-sm-10" style="text-align: left;">
                        Your credit card information is not stored on this server and is safe.
                    </div>
                    <div class="col-sm-1">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var stripe = Stripe('{{ env('STRIPE_KEY') }}');
    var elements = stripe.elements();
    var card = elements.create('card');

    // Add an instance of the card UI component into the `card-element` <div>
    card.mount('#card-element');

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

    function createToken() {
        stripe.createToken(card).then(function(result) {
            if (result.error) {
                // Inform the user if there was an error
                var errorElement = document.getElementById('card-errors');
                errorElement.textContent = result.error.message;
            } else {
                // Send the token to your server
                // console.log(result);
                stripeTokenHandler(result.token);
            }
        });
    };

    // Create a token when the form is submitted.
    var form = document.getElementById('payment-form');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        createToken();
    });

    card.addEventListener('change', function(event) {
        var displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });
</script>
