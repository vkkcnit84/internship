@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.' . $cfg['key'] . '.title_singular') }}
    </div>

    <div class="card-body">
        <div class="mb-2">
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.' . $cfg['key'] . '.fields.id') }}
                        </th>
                        <td>
                            {{ $record->id }}
                        </td>
                    </tr>
                    @foreach($cfg['fields'] as $field)
                        <tr>
                            <th>
                                {{ trans('cruds.' . $cfg['key'] . '.fields.' . $field['name']) }}
                            </th>
                            <td>
                                @php($row = $record)
                                @include('admin.crud._value')
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <a style="margin-top:20px;" class="btn btn-default" href="{{ route($cfg['route'] . '.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
        </div>
    </div>
</div>
@endsection
