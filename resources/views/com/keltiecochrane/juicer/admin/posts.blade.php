<h2>Posts</h2>
<p>
  You can delete posts from your feed here without removing them from your social
  media account.
</p>

<table class="wp-list-table widefat fixed striped">
  <thead>
    <tr>
      <th style="" class="manage-column column-source" id="source" scope="col">Source</th>
      <th style="" class="manage-column column-post" id="post" scope="col">Post</th>
      <th style="" class="manage-column column-date" id="date" scope="col">Date</th>
    </tr>
  </thead>

  <tbody>
    @foreach ($posts as $post)
      <tr>
        <td>
          <strong>{{ $post->source->source }}</strong>
          <div class="row-actions">
            <span class="edit">
              <a href="{{ $post->full_url }}" target="__blank" aria-label="View original">Original</a>
              |
            </span>
            <span class="trash">
              <a href="options-general.php?page={{ $__page->get('slug') }}&tab={{ $__section->slug }}&option_page={{ $__section->slug }}&action=com_keltiecochrane_juicer_delete_post&_wpnonce={{ wp_create_nonce('com_keltiecochrane_juicer_delete_post') }}&post_id={{ $post->id }}" class="submitdelete" aria-label="Delete post">Delete</a>
            </span>
          </div>
        </td>
        <td>{!! $post->message !!}</td>
        <td>{{ $post->external_created_at }}</td>
      </tr>
    @endforeach
  </tbody>
</table>
