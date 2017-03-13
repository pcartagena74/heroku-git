<?php
/**
 * Comment: The non-hosted form to handle credit card processing
 * Created: 3/12/2017
 */
?>
<div class="modal fade" id="login_modal" tabindex="-1" role="dialog" aria-labelledby="login_label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="login_label">Payment Information</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form class="panel-body">
                    <div class="row">
                        <div class="form-group col-xs-8">
                            <label class="control-label">Card Number</label>
                            <!--  Hosted Fields div container -->
                            <div class="form-control" id="card-number"></div>
                            <span class="helper-text"></span>
                        </div>
                        <div class="form-group col-xs-4">
                            <div class="row">
                                <label class="control-label col-xs-12">Expiration Date</label>
                                <div class="col-xs-6">
                                    <!--  Hosted Fields div container -->
                                    <div class="form-control" id="expiration-month"></div>
                                </div>
                                <div class="col-xs-6">
                                    <!--  Hosted Fields div container -->
                                    <div class="form-control" id="expiration-year"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-xs-6">
                            <label class="control-label">Security Code</label>
                            <!--  Hosted Fields div container -->
                            <div class="form-control" id="cvv"></div>
                        </div>
                        <div class="form-group col-xs-6">
                            <label class="control-label">Zipcode</label>
                            <!--  Hosted Fields div container -->
                            <div class="form-control" id="postal-code"></div>
                        </div>
                    </div>


                    <button value="submit" id="submit" class="btn btn-success btn-lg center-block">Pay with <span id="card-type">Card</span></button>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
