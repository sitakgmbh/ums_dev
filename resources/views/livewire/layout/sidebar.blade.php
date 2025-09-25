<!-- ========== Left Sidebar Start ========== -->
<div class="leftside-menu">

    <!-- Brand Logos -->
    <a href="{{ url('/') }}" class="logo logo-light">
        <span class="logo-lg">
            <img src="{{ asset('assets/images/logo.png') }}" alt="logo">
        </span>
        <span class="logo-sm">
            <img src="{{ asset('assets/images/logo-sm.png') }}" alt="small logo">
        </span>
    </a>
    <a href="{{ url('/') }}" class="logo logo-dark">
        <span class="logo-lg">
            <img src="{{ asset('assets/images/logo-dark.png') }}" alt="dark logo">
        </span>
        <span class="logo-sm">
            <img src="{{ asset('assets/images/logo-dark-sm.png') }}" alt="small logo">
        </span>
    </a>

    <div class="button-sm-hover" data-bs-toggle="tooltip" data-bs-placement="right" title="Show Full Sidebar">
        <i class="ri-checkbox-blank-circle-line align-middle"></i>
    </div>

    <div class="button-close-fullsidebar">
        <i class="ri-close-fill align-middle"></i>
    </div>

    <div class="h-100" id="leftside-menu-container" data-simplebar>
        <div class="leftbar-user">
            <a href="#">
                <img src="{{ asset('assets/images/users/avatar-1.jpg') }}" height="42" class="rounded-circle shadow-sm" alt="user-image">
                <span class="leftbar-user-name mt-2">{{ Auth::user()->name }}</span>
            </a>
        </div>

		<ul class="side-nav">
			@foreach(config('hyper.menu') as $section)
				@php
					// Admin-Section nur für Admins sichtbar machen
					$isAdminSection = $section['title'] === 'Admin';
				@endphp

				@if(!$isAdminSection || auth()->user()?->hasRole('admin'))
					<li class="side-nav-title">{{ $section['title'] }}</li>

					@foreach($section['items'] as $item)
						@php
							$id = \Illuminate\Support\Str::slug($item['label'], '_') . '_' . uniqid();
						@endphp

						@if(isset($item['children']) && is_array($item['children']))
							<li class="side-nav-item">
								<a data-bs-toggle="collapse" href="#{{ $id }}" aria-expanded="false"
								   aria-controls="{{ $id }}" class="side-nav-link">
									@isset($item['icon'])<i class="{{ $item['icon'] }}"></i>@endisset
									<span>{{ $item['label'] }}</span>
									<span class="menu-arrow"></span>
								</a>
								<div class="collapse" id="{{ $id }}">
									<ul class="side-nav-second-level">
										@foreach($item['children'] as $child)
											<li>
												<a href="{{ isset($child['url']) ? url($child['url']) : '#' }}">
													@isset($child['icon'])<i class="{{ $child['icon'] }}"></i>@endisset
													{{ $child['label'] ?? 'Unbenannt' }}
												</a>
											</li>
										@endforeach
									</ul>
								</div>
							</li>
						@else
							<li class="side-nav-item">
								<a href="{{ isset($item['url']) ? url($item['url']) : '#' }}" class="side-nav-link">
									@isset($item['icon'])<i class="{{ $item['icon'] }}"></i>@endisset
									<span>{{ $item['label'] ?? 'Unbenannt' }}</span>
									@isset($item['badge'])
										<span class="badge text-bg-secondary float-end">{{ $item['badge'] }}</span>
									@endisset
								</a>
							</li>
						@endif
					@endforeach
				@endif
			@endforeach
		</ul>

        <div class="clearfix"></div>
    </div>
</div>
<!-- ========== Left Sidebar End ========== -->
