@extends('layouts.app')

@section('title', 'Editar inscrição de piloto')
@section('subtitle', 'Atualize os dados da inscrição pública sem perder o histórico de pagamento.')

@section('content')
    <form method="POST" action="{{ route('admin.pilot-registrations.update', $registration) }}" class="card p-4 space-y-4">
        @csrf @method('PUT')

        <div class="flex flex-wrap justify-between items-center gap-3">
            <div class="section-title mb-0">Dados da inscrição</div>
            <div class="flex gap-2 items-center">
                <span class="{{ $registration->payment_status ? 'badge-green' : 'badge-red' }}">{{ $registration->payment_status ? 'Pago' : 'Não pago' }}</span>
                <a href="{{ route('admin.pilot-registrations.show', $registration) }}" class="btn-outline btn-sm">Ver detalhes</a>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div class="field">
                <label for="full_name" class="form-label">Nome completo</label>
                <input id="full_name" type="text" name="full_name" value="{{ old('full_name', $registration->full_name) }}" class="form-input" required>
            </div>
            <div class="field">
                <label for="whatsapp" class="form-label">WhatsApp</label>
                <input id="whatsapp" type="text" name="whatsapp" value="{{ old('whatsapp', $registration->whatsapp) }}" class="form-input" required>
            </div>
            <div class="field">
                <label for="cpf" class="form-label">CPF</label>
                <input id="cpf" type="text" name="cpf" value="{{ old('cpf', $registration->cpf) }}" class="form-input" required>
            </div>
            <div class="field">
                <label for="email" class="form-label">E-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email', $registration->email) }}" class="form-input" required>
            </div>
        </div>

        <div class="field">
            <label for="address" class="form-label">Endereço</label>
            <textarea id="address" name="address" class="form-textarea" rows="2" required>{{ old('address', $registration->address) }}</textarea>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div class="field">
                <label class="form-label">Já andou de kart antes?</label>
                <div class="flex gap-4 pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="has_kart_experience" id="kart_yes" value="1"
                            @checked((string) old('has_kart_experience', $registration->has_kart_experience ? '1' : '0') === '1') required>
                        <span class="text-sm font-semibold">Sim</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="has_kart_experience" id="kart_no" value="0"
                            @checked((string) old('has_kart_experience', $registration->has_kart_experience ? '1' : '0') === '0') required>
                        <span class="text-sm font-semibold">Não</span>
                    </label>
                </div>
            </div>
            <div class="field">
                <label class="form-label">Já disputou algum campeonato?</label>
                <div class="flex gap-4 pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="has_championship_experience" id="champ_yes" value="1"
                            @checked((string) old('has_championship_experience', $registration->has_championship_experience ? '1' : '0') === '1') required>
                        <span class="text-sm font-semibold">Sim</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="has_championship_experience" id="champ_no" value="0"
                            @checked((string) old('has_championship_experience', $registration->has_championship_experience ? '1' : '0') === '0') required>
                        <span class="text-sm font-semibold">Não</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div class="field">
                <label for="weight_kg" class="form-label">Peso (kg)</label>
                <input id="weight_kg" type="text" name="weight_kg" value="{{ old('weight_kg', $registration->weight_kg) }}" class="form-input" required>
            </div>
            <div class="field">
                <label for="age" class="form-label">Idade</label>
                <input id="age" type="number" min="1" name="age" value="{{ old('age', $registration->age) }}" class="form-input" required>
            </div>
            <div class="field">
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
        </div>

        <div class="field">
            <label for="notes" class="form-label">Observações internas</label>
            <textarea id="notes" name="notes" class="form-textarea" rows="3">{{ old('notes', $registration->notes) }}</textarea>
        </div>

        <div class="flex gap-2 pt-2 border-t border-ksa-border">
            <button class="btn-primary" data-submitting-label="Salvando...">Salvar inscrição</button>
            <a href="{{ route('admin.pilot-registrations.show', $registration) }}" class="btn-ghost">Cancelar</a>
        </div>
    </form>
@endsection
