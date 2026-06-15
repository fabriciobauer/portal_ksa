@extends('layouts.app')

@section('title', 'Editar inscrição de piloto')
@section('subtitle', 'Atualize os dados da inscrição pública sem perder o histórico de pagamento.')

@section('content')
    <form method="POST" action="{{ route('admin.pilot-registrations.update', $registration) }}" class="content-card p-4">
        @csrf
        @method('PUT')

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div class="section-title mb-0">Dados da inscrição</div>
            <div class="d-flex gap-2">
                <span class="badge {{ $registration->payment_status ? 'text-bg-success' : 'text-bg-danger' }}">
                    {{ $registration->payment_status ? 'Pago' : 'Não pago' }}
                </span>
                <a href="{{ route('admin.pilot-registrations.show', $registration) }}" class="btn btn-outline-dark btn-sm">Ver detalhes</a>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="full_name" class="form-label">Nome completo</label>
                <input id="full_name" type="text" name="full_name" value="{{ old('full_name', $registration->full_name) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="whatsapp" class="form-label">WhatsApp</label>
                <input id="whatsapp" type="text" name="whatsapp" value="{{ old('whatsapp', $registration->whatsapp) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="cpf" class="form-label">CPF</label>
                <input id="cpf" type="text" name="cpf" value="{{ old('cpf', $registration->cpf) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">E-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email', $registration->email) }}" class="form-control" required>
            </div>
            <div class="col-12">
                <label for="address" class="form-label">Endereço</label>
                <textarea id="address" name="address" class="form-control" rows="2" required>{{ old('address', $registration->address) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label d-block">Já andou de kart antes?</label>
                <div class="d-flex gap-3 pt-2">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="has_kart_experience" id="kart_yes" value="1" @checked((string) old('has_kart_experience', $registration->has_kart_experience ? '1' : '0') === '1') required>
                        <label class="form-check-label" for="kart_yes">Sim</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="has_kart_experience" id="kart_no" value="0" @checked((string) old('has_kart_experience', $registration->has_kart_experience ? '1' : '0') === '0') required>
                        <label class="form-check-label" for="kart_no">Não</label>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label d-block">Já disputou algum campeonato?</label>
                <div class="d-flex gap-3 pt-2">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="has_championship_experience" id="champ_yes" value="1" @checked((string) old('has_championship_experience', $registration->has_championship_experience ? '1' : '0') === '1') required>
                        <label class="form-check-label" for="champ_yes">Sim</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="has_championship_experience" id="champ_no" value="0" @checked((string) old('has_championship_experience', $registration->has_championship_experience ? '1' : '0') === '0') required>
                        <label class="form-check-label" for="champ_no">Não</label>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <label for="weight_kg" class="form-label">Peso</label>
                <input id="weight_kg" type="text" name="weight_kg" value="{{ old('weight_kg', $registration->weight_kg) }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label for="age" class="form-label">Idade</label>
                <input id="age" type="number" min="1" name="age" value="{{ old('age', $registration->age) }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label for="pilot_registration_category_id" class="form-label">Categoria</label>
                <select id="pilot_registration_category_id" name="pilot_registration_category_id" class="form-select" required>
                    <option value="">Selecione</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('pilot_registration_category_id', $registration->pilot_registration_category_id) === (string) $category->id)>
                            {{ $category->name }}{{ $category->is_active ? '' : ' (inativa)' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label for="notes" class="form-label">Observações internas</label>
                <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes', $registration->notes) }}</textarea>
            </div>
        </div>

        <div class="mt-4 d-flex flex-wrap gap-2">
            <button class="btn btn-primary">Salvar inscrição</button>
            <a href="{{ route('admin.pilot-registrations.show', $registration) }}" class="btn btn-outline-dark">Cancelar</a>
        </div>
    </form>
@endsection
