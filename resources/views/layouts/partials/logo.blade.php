<a href="{{ route('admin.dashboard') }}" class="logo logo-dark">
    <span class="logo-sm">
        <img src="{{ asset('storage/' . $site_logo) }}" alt="Logo nhỏ" height="22">
    </span>
    <span class="logo-lg">
        <img src="{{ asset('storage/' . $site_logo) }}" alt="Logo lớn" height="17">
    </span>
</a>

<!-- Light Logo -->
<a href="{{ route('admin.dashboard') }}" class="logo logo-light">
    <span class="logo-sm">
        <img src="{{ asset('storage/' . $site_logo) }}" alt="Logo nhỏ" height="22">
    </span>
    <div class="custom-flex">
        <span class="logo-lg">
            <img src="{{ asset('storage/' . $site_logo) }}" alt="Logo lớn" width="40" height="40">
        </span>
        <span id="custom-text-logo" class="custom-text-logo">{{ $site_name }}</span>
    </div>
</a>
