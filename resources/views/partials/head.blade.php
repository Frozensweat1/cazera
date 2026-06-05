 @php
     $settings = \App\Support\WebsiteContent::settings();
     $brandName = $settings?->business_name ?: config('app.name', 'Cazera');
     $baseTitle = $settings?->meta_title ?: $brandName;
     $pageTitle = trim($__env->yieldContent('pageTitle'));
     $documentTitle = $pageTitle !== '' ? $pageTitle . ' | ' . $baseTitle : $baseTitle . ' Backoffice';
     $description = $settings?->meta_description ?: 'Hospitality ERP, POS, inventory, finance, and website management system.';
     $favicon = $settings?->favicon ? \App\Support\WebsiteContent::assetPath($settings->favicon) : asset('favicon.ico');
 @endphp
 <meta charset="utf-8">
 <meta http-equiv="X-UA-Compatible" content="IE=edge">
 <title>{{ $documentTitle }}</title>
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <meta name="description" content="{{ $description }}">
 <meta name="application-name" content="{{ $brandName }}">
 <meta name="theme-color" content="#0f0d0a">
 <link rel="icon" type="image/x-icon" href="{{ $favicon }}">
 <link rel="apple-touch-icon" href="{{ $favicon }}">
 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
 <link href="css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
 <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('assets/css/perfect-scrollbar.min.css') }}">
 <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('assets/css/style.css') }}">
 <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('assets/css/backoffice-overrides.css') }}">
 <link defer="" rel="stylesheet" type="text/css" media="screen" href="{{ asset('assets/css/animate.css') }}">
 <script src="{{ asset('assets/js/perfect-scrollbar.min.js') }}"></script>
 <script src="{{ asset('assets/js/popper.min.js') }}"></script>
 <script src="{{ asset('assets/js/tippy-bundle.umd.min.js') }}"></script>
 <script src="{{ asset('assets/js/sweetalert.min.js') }}"></script>
