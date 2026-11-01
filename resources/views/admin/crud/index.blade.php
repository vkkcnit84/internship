@extends('layouts.admin')
@section('content')
@can($cfg['key'] . '_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route($cfg['route'] . '.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.' . $cfg['key'] . '.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.' . $cfg['key'] . '.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-crud">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.' . $cfg['key'] . '.fields.id') }}
                        </th>
                        @foreach($cfg['fields'] as $field)
                            @if($field['index'] ?? true)
                                <th>
                                    {{ trans('cruds.' . $cfg['key'] . '.fields.' . $field['name']) }}
                                </th>
                            @endif
                        @endforeach
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr data-entry-id="{{ $row->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $row->id }}
                            </td>
                            @foreach($cfg['fields'] as $field)
                                @if($field['index'] ?? true)
                                    <td>
                                        @include('admin.crud._value')
                                    </td>
                                @endif
                            @endforeach
                            <td>
                                @can($cfg['key'] . '_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route($cfg['route'] . '.show', $row->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can($cfg['key'] . '_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route($cfg['route'] . '.edit', $row->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can($cfg['key'] . '_delete')
                                    <form action="{{ route($cfg['route'] . '.destroy', $row->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@section('scripts')
@parent
<script>
    $(function () {
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can($cfg['key'] . '_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route($cfg['route'] . '.massDestroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
          return $(entry).data('entry-id')
      });

      if (ids.length === 0) {
        alert('{{ trans('global.datatables.zero_selected') }}')

        return
      }

      if (confirm('{{ trans('global.areYouSure') }}')) {
        $.ajax({
          headers: {'x-csrf-token': _token},
          method: 'POST',
          url: config.url,
          data: { ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
          .fail(function (xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) || 'Error')
          })
      }
    }
  }
  dtButtons.push(deleteButton)
@endcan

  $.extend(true, $.fn.dataTable.defaults, {
    order: [[ 1, 'desc' ]],
    pageLength: 100,
  });
  $('.datatable-crud:not(.ajaxTable)').DataTable({ buttons: dtButtons })
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
})

</script>
@endsection
