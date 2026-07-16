{{-- resources/views/components/navbar.blade.php --}}
<div class="header-container container-xxl">
    <header class="header navbar navbar-expand-sm expand-header">

        <a href="javascript:void(0);" class="sidebarCollapse">
            <x-feather-icon name="menu" />
        </a>

        <!-- <div class="search-animated toggle-search">
            <x-feather-icon name="search" />
            <form class="form-inline search-full form-inline search" role="search">
                <div class="search-bar">
                    <input type="text" class="form-control search-form-control ml-lg-auto" placeholder="Search...">
                    <x-feather-icon name="x" class="search-close" />
                </div>
            </form>
            <span class="badge badge-secondary">Ctrl + /</span>
        </div> -->

        <ul class="navbar-item flex-row ms-lg-auto ms-0">
            @include('components.navbar.theme-toggle')
            @include('components.navbar.user-profile-dropdown')

        </ul>
    </header>
</div>
