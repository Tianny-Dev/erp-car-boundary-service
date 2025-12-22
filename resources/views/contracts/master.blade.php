<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ config('app.name', 'Laravel') }}</title>
  @include('contracts.styles')
</head>
<body>

@include('contracts.sections.intro')

@include('contracts.sections.article1')

@include('contracts.sections.article2')

@include('contracts.sections.signatures')

</body>
</html>
