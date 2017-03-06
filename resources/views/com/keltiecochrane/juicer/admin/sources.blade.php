<form action="options-general.php?page={{ $__page->get('slug') }}&tab={{ $__section->slug }}" method="post" class="themosis-core-page">
  {!! wp_referer_field(false) !!}
  <input type="hidden" name="option_page" value="{{ $__section->slug }}" />
  <input type="hidden" name="action" value="com_keltiecochrane_juicer_create_source" />
  <input type="hidden" name="_wpnonce" value="{{ wp_create_nonce('com_keltiecochrane_juicer_create_source') }}" />

  <h2>Create Source</h2>
  <p>You can add additional social media sources to be used across the site here.</p>

  <table class="form-table">
    <tbody>
      <tr>
        <th scope="row">
          <label for="source-network">Social Network</label>
        </th>
        <td>
          <select id="source-network" name="network" required>
            <option disabled selected>Select a Social Network</option>
            @foreach ($networks as $slug => $name)
              <option value="{{ $slug }}">{{ $name }}</option>
            @endforeach
          </select>
          <span class="description">Select the social network for the new source.</span>
        </td>
      </tr>

      <tr>
        <th scope="row">
          <label for="source-term-type">Term Type</label>
        </th>
        <td>
          <select id="source-term-type" name="term_type" required>
            <option disabled selected>Select a Term Type</option>
            @foreach ($term_types as $slug => $name)
              <option value="{{ $slug }}">{{ $name }}</option>
            @endforeach
          </select>
          <span class="description">Select the term type for the new source.</span>
        </td>
      </tr>

      <tr>
        <th scope="row">
          <label for="source-term">Term</label>
        </th>
        <td>
          <input id="source-term" name="term" required />
          <span class="description">Enter the term to apply to the new source.</span>
        </td>
      </tr>
    </tbody>
  </table>

  @php submit_button('Create Source') @endphp
</form>

<h2>Sources</h2>

<table class="wp-list-table widefat fixed striped">
  <thead>
    <tr>
      <th style="" class="manage-column column-term" id="term" scope="col">Term</th>
      <th style="" class="manage-column column-network" id="network" scope="col">Social Network</th>
    </tr>
  </thead>

  <tbody>
    @foreach ($sources as $source)
      <tr>
        <td>
          <strong>{!! $source->generateAnchor() !!}</strong>
          <div class="row-actions">
            <span class="trash">
              <a href="options-general.php?page={{ $__page->get('slug') }}&tab={{ $__section->slug }}&option_page={{ $__section->slug }}&action=com_keltiecochrane_juicer_delete_source&_wpnonce={{ wp_create_nonce('com_keltiecochrane_juicer_delete_source') }}&source_id={{ $source->id }}" class="submitdelete" aria-label="Delete source">Delete</a>
            </span>
          </div>
        </td>
        <td>{{ $source->source }}</td>
      </tr>
    @endforeach
  </tbody>
</table>
