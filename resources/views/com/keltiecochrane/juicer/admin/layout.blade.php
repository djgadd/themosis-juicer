<div class="wrap">
    <h1>{{ $__page->get('title') }}</h1>

    @php
      // Show errors
      if (empty($__page->get('parent')) || $__page->get('parent') !== 'options-general.php') {
        settings_errors();
      }

      // Render the tabs
      $__page->renderTabs();

      // Doing this inside call_user_func so we don't pollute the global
      call_user_func(function () use ($__sections) {
        $firstSection = $__sections[0]->getData();
        $activeTab =  isset($_GET['tab']) ? $_GET['tab'] : $firstSection['slug'];

        return collect($__sections)->first(function ($section) use ($activeTab) {
          $data = $section->getData();
          return $activeTab === $data['slug'];
        });
      })->with('__page', $__page)->render();
    @endphp

</div>
