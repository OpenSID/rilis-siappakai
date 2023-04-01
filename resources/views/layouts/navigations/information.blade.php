<div class="accordion" id="accordionExample">
    @foreach ($informations as $info)
        <div class="accordion-item">
            <h2 class="accordion-header" id="{{ $info['id'] }}">
                <button class="accordion-button {{ $info['collapsed'] }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $info['target'] }}" aria-expanded="true" aria-controls="{{ $info['target'] }}">
                    {{ $info['title'] }}
                </button>
            </h2>
            <div id="{{ $info['target'] }}" class="accordion-collapse collapse {{ $info['show'] }}" aria-labelledby="{{ $info['id'] }}" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    {{ $info['description'] }}
                    <a href="{{ $info['link'] }}" target="_blank">Selengkapnya di Panduan.</a>
                </div>
            </div>
        </div>
    @endforeach
</div>
