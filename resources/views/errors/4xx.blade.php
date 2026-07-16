@php
    $statusCode = $exception->getStatusCode();
@endphp

@include('errors.http-error', [
    'statusCode' => $statusCode,
    'errorTitle' => 'Client Error!',
    'errorMessage' => $exception->getMessage() ?: 'There was a problem with your request.',
    'backUrl' => url()->previous() !== url()->current() ? url()->previous() : url('/'),
    'backText' => 'Go Back',
    'showHomeButton' => true,
    'showImage' => true
])
