<?php
/**
 * Comment: A pop-up to collect user charge card info and send to stripe
 * Created: 3/20/2017
 */

Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

// Token is created using Stripe.js or Checkout!
// Get the payment token submitted by the form:
$token = $_POST['stripeToken'];

// Charge the user's card:
$charge = \Stripe\Charge::create(array(
"amount" => 1000,
"currency" => "usd",
"description" => "Example charge",
"source" => $token,
));
?>

<script>
var stripe = Stripe(env('STRIPE_KEY'));
var elements = stripe.elements();
</script>
