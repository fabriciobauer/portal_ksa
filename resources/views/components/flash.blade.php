@if (session('status'))
    <div
        class="alert alert-success alert-dismissible fade show flash-alert"
        role="alert"
        data-flash-alert
        data-auto-dismiss="4500"
        tabindex="-1"
    >
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger flash-alert" data-flash-errors tabindex="-1">
        <div class="fw-semibold mb-2">Verifique os dados informados:</div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
