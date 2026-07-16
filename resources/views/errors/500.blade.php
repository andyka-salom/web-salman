@include('errors.http-error', [
    'statusCode' => 500,
    'errorTitle' => 'Server Error!',
    'errorMessage' => 'Something went wrong on our end. We\'re working to fix it!',
    'backUrl' => url('/'),
    'backText' => 'Go to Homepage',
    'showHomeButton' => false,
    'showImage' => true
])
