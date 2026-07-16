@php
    $statusCode = $exception->getStatusCode();
@endphp

@include('errors.http-error', [
    'statusCode' => $statusCode,
    'errorTitle' => 'Server Error!',
    'errorMessage' => 'Something went wrong on our server. Please try again later.',
    'backUrl' => url('/'),
    'backText' => 'Go to Homepage',
    'showHomeButton' => false,
    'showImage' => true
])
