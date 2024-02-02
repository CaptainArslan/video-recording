<div class="mb-3 {{ $attr['col'] ? 'col-' . $attr['col'] : '' }}">
    <label for="{{ $attr['name'] }}" class="form-label">{{ $attr['label'] }} @if ($attr['required'] == true)
            <span class="text-danger">*</span>
        @endif </label>
    @if ($attr['type'] == 'textarea')
        <textarea name="{{ $attr['name'] }}" id="{{ $attr['name'] }}" cols="30" rows="10">{{ $attr['value'] }}</textarea>
    @elseif($attr['type'] == 'select')
        {{-- @dd($attr['options']) --}}
        <select name="{{ $attr['name'] }}" id="{{ $attr['name'] }}" class="form-control">
            @foreach ($attr['options'] as $key => $option)
                <option value="{{ $key }}" {{ $attr['value'] == $key ? 'selected' : '' }}>
                    {{ $option }}</option>
            @endforeach
        </select>
    @else
        <input type="{{ $attr['type'] }}" class="form-control" value="{{ $attr['value'] }}"
            name="{{ $attr['name'] }}" id="{{ $attr['name'] }}" aria-describedby="crm-client"
            required="{{ $attr['required'] ?? '' }}">
        @error($attr['name'])
            <span class="text-danger">{{ $message }}</span>
        @enderror
    @endif
</div>
