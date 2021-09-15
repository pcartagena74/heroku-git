<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mail Tracker</title>
    @if(env('APP_ENV') == 'local')
      <link href="{{ str_replace('https', 'http', env('APP_URL')) }}/images/mCentric_dev.ico" rel="icon"/>
    @else
      <link href="{{ str_replace('https', 'http', env('APP_URL')) }}/images/mCentric.ico" rel="icon"/>
    @endif
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  </head>
  <body>
  	@yield('content')
  </body>
</html>