{{-- Document footer shown on every printed page. --}}
<div class="doc-footer">
    <span>Printed {{ now()->format('d M Y H:i') }} by {{ auth()->user()?->name }}</span>
    <span>Powered by Tech Scales Co. Ltd</span>
</div>
