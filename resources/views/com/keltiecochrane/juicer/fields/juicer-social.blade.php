<ul class="acf-checkbox-list acf-bl">
  @foreach ($field['sources'] as $value => $label)
    <li>
      <label>
        <input type="checkbox" id="acf-{{ $field['key'] }}" name="{{ $field['name'] }}[]" value="{{ $value }}" @if (!empty($field['value']) && in_array($value, $field['value'])) checked @endif />
        {!! $label !!}
      </label>
    </li>
  @endforeach
</ul>
