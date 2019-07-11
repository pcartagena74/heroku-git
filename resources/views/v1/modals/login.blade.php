<?php
/**
 * Comment: a Login Modal that will submit silently
 * Created: 3/3/2017
 *
 * @param: $id
 */
if(!isset($id)){
    $id = 'login_modal';
}
if(!isset($msg)){
    $msg = trans('messages.modals.login_msg');
}
?>

<div class="modal fade" id="{{ $id }}" tabindex="-1" role="dialog" aria-labelledby="login_label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="login_label">mCentric @lang('messages.modals.loginform')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form class="form-horizontal" role="form" method="POST" action="{{ route('login') }}">
                    {{ csrf_field() }}
                    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                        <label  for="email" class="col-md-4 control-label">@lang('messages.fields.email')</label>

                        <div class="col-md-6">
                            <input id="email" type="email" class="form-control" name="email" autocomplete="username email"
                                   value="{{ old('email') }}" required autofocus>

                            @if ($errors->has('email'))
                                <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                        <label for="password" class="col-md-4 control-label">@lang('messages.fields.password')</label>

                        <div class="col-md-6">
                            <input id="password" type="password" class="form-control" name="password" autocomplete="current-password" required>

                            @if ($errors->has('password'))
                                <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-4">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                           @lang('messages.modals.remember')
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-8 col-md-offset-4">
                            <button type="submit" class="btn btn-primary">
                                @lang('messages.buttons.login')
                            </button>

                            <a href="#reset_modal" class="btn btn-link" data-toggle="modal" data-dismiss="modal" data-target="#reset_modal">
                                @lang('messages.modals.forgot')
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="container">
                <div class="col-sm-10" style="text-align: left;">
                    {!! $msg !!}
                </div>
                <div class="col-sm-1">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">@lang('messages.buttons.close')</button>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
