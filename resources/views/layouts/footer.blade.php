<footer class="main-footer">
    <strong>Copyright &copy; {{ date('Y') }} <a href="{{ url('dashboard') }}">{{ Config::get('constants.SITE_NAME') }}</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> {{ Config::get('constants.VERSION') }}
    </div>
  </footer>