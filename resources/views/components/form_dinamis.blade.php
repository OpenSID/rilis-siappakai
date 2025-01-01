<div class="form-group row mb-2 {{ $jenis == 'hidden' ? 'd-none' : '' }}">

    <label class="col-form-label col-md-3 col-sm-3">
        {{ $label }} {!! $required == 1 ? '<span class="required">*</span>' : '' !!}
    </label>

    @switch($jenis)
        @case('text')
            <div class="col-md-5 col-sm-5 ">
                <input type="text" id="{{ $key }}" name="{{ $key }}" class="form-control {{ $class }}"
                    {{ $required == 1 ? 'required' : '' }} value="{{ $value }}">
            </div>
            <span class="d-flex align-items-center col-md-4 col-sm-4 ps-2">{{ $keterangan }}</span>
            @error($key)
                <div class="text-danger mt-1 d-block">{{ $message }}</div>
            @enderror
        @break

        @case('hidden')
            <div class="col-md-4 col-sm-4">
                <input type="hidden" id="{{ $key }}" name="{{ $key }}"
                    class="form-control {{ $class }}" required value="{{ $value }}">
            </div>
        @break

        @case('option')
            <div class="col-md-5 col-sm-5 ">
                <select name="{{ $key }}" id="{{ $key }}"
                    class="form-select @error($key) is-invalid @enderror">
                    @if ($placeholder)
                        <option value="" disabled>{{ $placeholder }}</option>
                    @endif
                    @if (!empty($options))
                        @foreach ($options as $item)
                            <option value="{{ $item['value'] }}" @selected($item['value'] == $value)>
                                {{ $item['label'] }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <span class="d-flex align-items-center col-md-4 col-sm-4 ps-2">{{ $keterangan }}</span>
        @break

        @case('option_multiple')
            <div class="col-md-5 col-sm-5 ">
                <select name="{{ $key }}[]" id="{{ $key }}"
                    class="form-select {{ $class }} @error($key) is-invalid @enderror" multiple="multiple" >
                    @if ($placeholder)
                        <option value="" disabled>{{ $placeholder }}</option>
                    @endif
                    @if (!empty($options))
                        @foreach ($options as $item)
                            <option value="{{ $item['value'] }}" @selected($item['value'] == $value)>
                                {{ $item['label'] }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <span class="d-flex align-items-center col-md-4 col-sm-4 ps-2">{{ $keterangan }}</span>
        @break

        @case('number')
            <div class="col-md-5 col-sm-5 ">
                <input type="number" id="{{ $key }}" name="{{ $key }}"
                    class="form-control {{ $class }}" {{ $required == 1 ? 'required' : '' }}
                    value="{{ $value }}">
            </div>
            <span class="d-flex align-items-center col-md-4 col-sm-4 ps-2">{{ $keterangan }}</span>
            @error($key)
                <div class="text-danger mt-1 d-block">{{ $message }}</div>
            @enderror
        @break

        @case('option_multiple')
            <div class="col-form-label col-md-5 col-sm-5">
                <textarea class="form-control  @error($key) is-invalid @enderror" name="{{ $key }}" id="{{ $key }}"></textarea>
            </div>
            <span class="d-flex align-items-center col-md-4 col-sm-4 ps-2">{{ $keterangan }}</span>
        @break

        @default
    @endswitch
</div>
