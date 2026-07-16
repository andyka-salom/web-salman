@include('errors.http-error', [
    'statusCode' => 404,
    'errorTitle' => 'Page Not Found!',
    'errorMessage' => 'The page you requested was not found!',
    'backUrl' => url()->previous() !== url()->current() ? url()->previous() : url('/'),
    'backText' => 'Go Back',
    'showHomeButton' => true,
    'showImage' => true
])
