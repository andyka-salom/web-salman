@include('errors.http-error', [
    'statusCode' => 403,
    'errorTitle' => 'Access Forbidden!',
    'errorMessage' => 'You don\'t have permission to access this resource.',
    'backUrl' => url('/'),
    'backText' => 'Go to Homepage',
    'showHomeButton' => false,
    'showImage' => true
])
