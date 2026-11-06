<li @isset($item['id']) id="{{ $item['id'] }}" @endisset class="nav-item has-treeview {{ $item['submenu_class'] }}">

    {{-- Menu toggler --}}
    <a class="nav-link {{ $item['class'] }} @isset($item['shift']) {{ $item['shift'] }} @endisset"
       href="" {!! $item['data-compiled'] ?? '' !!}>

        <i class="nav-icon {{ $item['icon'] ?? 'far fa-fw fa-circle' }} {{
            isset($item['icon_color']) ? 'text-'.$item['icon_color'] : ''
        }}"></i>

        <p>
            {{ $item['text'] }}
            <i class="fas fa-angle-left right"></i>

            @isset($item['label'])
                <span class="badge badge-{{ $item['label_color'] ?? 'primary' }} right">
                    {{ $item['label'] }}
                </span>
            @endisset

            @php $badgeMenunggu = ($item['badge'] ?? null) === 'menunggu' ? \App\Models\Permohonan::countMenunggu() : 0; @endphp
            @if($badgeMenunggu > 0)
                <span class="right" title="{{ $badgeMenunggu }} permohonan menunggu"
                      style="display:inline-flex;align-items:center;justify-content:center;min-width:1.35rem;height:1.35rem;padding:0 .35rem;border-radius:9999px;background:#ef4444;color:#fff;font-size:11px;font-weight:700;line-height:1;">
                    {{ $badgeMenunggu }}
                </span>
            @endif
        </p>

    </a>

    {{-- Menu items --}}
    <ul class="nav nav-treeview">
        @each('adminlte::partials.sidebar.menu-item', $item['submenu'], 'item')
    </ul>

</li>
