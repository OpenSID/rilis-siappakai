<div class="col-md-4 col-sm-6">
    <div class="card card-primary">
        <div class="card-header">
            {{ $item['name'] ?? '__name__' }}
        </div>
        <div class="card-body" style="min-height:200px">
            <div class="row">
                <div class="col-md-9">
                    {{ $item['description'] ?? '__description__' }}
                </div>
                <div class="col-md-3">
                    <div class="row">
                        <img width="70px" class="img-thumbnail" src="{{ $item['thumbnail'] ?? '__thumbnail__' }}" alt="Static Pages">
                        <div class="price-tag">{{ $item['price'] ?? '__price__' }}</div>
                    </div>
                </div>
            </div>

        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-md-6">
                    {!! $button ?? '__button__' !!}
                </div>
                <div class="col-md-6">
                    <div class="text-right" style="margin-top:10px">
                        {{ $item['totalInstall'] ?? '__totalInstall__' }} Terpasang
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
