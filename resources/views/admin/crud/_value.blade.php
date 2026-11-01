@php
    $raw = $row->{$field['name']};
    if ($field['type'] === 'select') {
        $related = $row->{$field['relation']};
        $display = $related ? ($related->full_name ?? $related->name) : '';
    } elseif ($field['type'] === 'checkbox') {
        $display = $raw ? trans('global.yes') : trans('global.no');
    } elseif ($field['type'] === 'image') {
        $display = null;
    } else {
        $display = $raw;
    }
@endphp
{{ $display }}
@if($field['type'] === 'image' && $raw)
    <img src="{{ asset('storage/' . $raw) }}" alt="" style="max-height: 40px; max-width: 120px;">
@endif
