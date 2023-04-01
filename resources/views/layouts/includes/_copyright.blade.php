@php
    $year = date('Y');
    $create = "2022";
@endphp
<span>
    Hak Cipta &copy; {{ $year > $create ? $create. ' - ' .$year : $create }}
    <a href="http://opendesa.id">OpenDesa</a>.
</span>
