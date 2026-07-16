{{-- resources/views/components/footer.blade.php --}}
<div class="footer-wrapper {{ $class ?? '' }}">
    <div class="footer-section f-section-1">
        <p class="">Copyright © <span class="dynamic-year">{{ date('Y') }}</span>
            <a target="_blank" href="/">PHM</a>, PO/SUP.
        </p>
    </div>
    <div class="footer-section f-section-2">
        <p class="">Coded with <x-feather-icon name="heart" /></p>
    </div>
</div>
