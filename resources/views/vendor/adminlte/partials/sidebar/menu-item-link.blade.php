
@php
    $user = Auth::user();
    $hasRole = true; // Default to true if 'role' key is not set

    // Check if 'role' key exists and set $hasRole accordingly
    if (isset($item['role'])) {
        if (is_array($item['role'])) {
            $hasRole = $user->hasAnyRole($item['role']);
        } else {
            $hasRole = $user->hasRole($item['role']);
        }
    }
@endphp

@if ($hasRole)
<li @isset($item['id']) id="{{ $item['id'] }}" @endisset class="nav-item">

    <a class="nav-link {{ $item['class'] }} @isset($item['shift']) {{ $item['shift'] }} @endisset"
       href="{{ $item['href'] }}" @isset($item['target']) target="{{ $item['target'] }}" @endisset
       {!! $item['data-compiled'] ?? '' !!}>

        <i class="{{ $item['icon'] ?? 'far fa-fw fa-circle' }} {{
            isset($item['icon_color']) ? 'text-'.$item['icon_color'] : ''
        }}"></i>

        <p>
            {{ $item['text'] }}

            @isset($item['label'])
                <span class="badge badge-{{ $item['label_color'] ?? 'primary' }} right">
                    {{ $item['label'] }}
                </span>
            @endisset
        </p>

    </a>

</li>
@endif
