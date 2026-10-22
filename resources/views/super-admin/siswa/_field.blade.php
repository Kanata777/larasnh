@php
    $type = $type ?? 'text';
    $id = 'f-' . $name;
    $val = old($name, $data[$name] ?? '');
    $bad = $errors->has($name) ? 'invalid' : '';
@endphp

<div class="field {{ ! empty($full) ? 'full' : '' }}">
    <label for="{{ $id }}">{{ $label }}@if(! empty($required)) <span class="req">*</span>@endif</label>

    @if($type === 'select')
        <select id="{{ $id }}" name="{{ $name }}" class="{{ $bad }}" {{ ! empty($required) ? 'required' : '' }}>
            <option value="">{{ $placeholder ?? 'Pilih' }}</option>
            @foreach($options as $key => $text)
                <option value="{{ $key }}" {{ (string) $val === (string) $key ? 'selected' : '' }}>{{ $text }}</option>
            @endforeach
        </select>
    @elseif($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" class="{{ $bad }}" maxlength="{{ $max ?? 255 }}"
                  placeholder="{{ $placeholder ?? '' }}">{{ $val }}</textarea>
    @else
        <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}" value="{{ $val }}" class="{{ $bad }}"
               {{ ! empty($required) ? 'required' : '' }}
               @if(! empty($max)) maxlength="{{ $max }}" @endif
               @if(! empty($list)) list="{{ $list }}" @endif
               @if(! empty($inputmode)) inputmode="{{ $inputmode }}" @endif
               placeholder="{{ $placeholder ?? '' }}">
    @endif

    @error($name)
        <small class="err">{{ $message }}</small>
    @enderror
</div>