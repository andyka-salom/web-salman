{{-- resources/views/components/navbar/language-dropdown.blade.php --}}
<li class="nav-item dropdown language-dropdown">
    <a href="javascript:void(0);"
       class="nav-link dropdown-toggle"
       id="language-dropdown"
       data-bs-toggle="dropdown"
       aria-haspopup="true"
       aria-expanded="false">
        <img src="{{ asset('assets/img/1x1/us.svg') }}" class="flag-width" alt="flag">
    </a>
    <div class="dropdown-menu position-absolute" aria-labelledby="language-dropdown">
        <a class="dropdown-item d-flex" href="javascript:void(0);">
            <img src="{{ asset('assets/img/1x1/us.svg') }}" class="flag-width" alt="flag">
            <span class="align-self-center">&nbsp;English</span>
        </a>
        <a class="dropdown-item d-flex" href="javascript:void(0);">
            <img src="{{ asset('assets/img/1x1/tr.svg') }}" class="flag-width" alt="flag">
            <span class="align-self-center">&nbsp;Turkish</span>
        </a>
        <a class="dropdown-item d-flex" href="javascript:void(0);">
            <img src="{{ asset('assets/img/1x1/br.svg') }}" class="flag-width" alt="flag">
            <span class="align-self-center">&nbsp;Portuguese</span>
        </a>
        <a class="dropdown-item d-flex" href="javascript:void(0);">
            <img src="{{ asset('assets/img/1x1/in.svg') }}" class="flag-width" alt="flag">
            <span class="align-self-center">&nbsp;Hindi</span>
        </a>
        <a class="dropdown-item d-flex" href="javascript:void(0);">
            <img src="{{ asset('assets/img/1x1/de.svg') }}" class="flag-width" alt="flag">
            <span class="align-self-center">&nbsp;German</span>
        </a>
    </div>
</li>
