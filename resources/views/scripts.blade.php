{{-- Global configuration object --}}
@php
app()->setLocale('es');
$config = [
    'appName' => config('app.name'),
    'locale' => $locale = app()->getLocale(),
    'translations' => json_decode(file_get_contents(resource_path("lang/{$locale}.json")), true),
];
@endphp
<script>window.config = {!! json_encode($config); !!};</script>

{{-- Polyfill some features via polyfill.io --}}
@php
$polyfills = [
    'Promise',
    'Object.assign',
    'Object.values',
    'Array.prototype.find',
    'Array.prototype.findIndex',
    'Array.prototype.includes',
    'String.prototype.includes',
    'String.prototype.startsWith',
    'String.prototype.endsWith',
];
@endphp
<script src="https://cdn.polyfill.io/v2/polyfill.min.js?features={{ implode(',', $polyfills) }}"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAGr4repv7lxsgWh-RoL2cYJWQbVL-QVOY&libraries=places" ></script>
<script defer src="https://use.fontawesome.com/releases/v5.0.9/js/all.js" integrity="sha384-8iPTk2s/jMVj81dnzb/iFR2sdA7u06vHJyyLlAd4snFpCl/SnyUjRrbdJsw1pGIl" crossorigin="anonymous"></script>

{{-- Load the application scripts --}}
@if (app()->isLocal())
  <script src="{{ mix('js/app.js') }}"></script>
@else
  <script src="{{ mix('js/manifest.js') }}"></script>
  <script src="{{ mix('js/vendor.js') }}"></script>
  <script src="{{ mix('js/app.js') }}"></script>
@endif
