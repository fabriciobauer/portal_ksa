@extends('layouts.public')

@section('title', 'Inscrição de pilotos')

@section('content')
    @php
        $adminUrl = auth()->check() ? route('dashboard') : route('login');
        $adminLabel = auth()->check() ? 'Ir para o painel' : 'Login da administração';
    @endphp

    {{-- Hero bar --}}
    <div class="bg-ksa-navy text-white">
        <div class="max-w-6xl mx-auto px-4 py-6">
            <div class="flex justify-between items-center">
                <a href="{{ route('public.home') }}" class="text-sm font-semibold text-white/70 hover:text-white transition-colors">ksaracing.com.br</a>
                <a href="{{ $adminUrl }}" class="text-sm font-semibold border border-white/40 px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">{{ $adminLabel }}</a>
            </div>
        </div>
    </div>

    <main class="max-w-6xl mx-auto px-4 py-8">
        <div class="grid lg:grid-cols-3 gap-6 items-start">
            {{-- Sidebar --}}
            <div class="bg-ksa-navy text-white rounded-2xl p-6">
                <p class="text-xs font-bold uppercase tracking-widest text-white/50 mb-1">Inscrição pública</p>
                <h1 class="text-xl font-bold mb-3">Seu primeiro passo para correr com a KSA</h1>
                <p class="text-sm text-white/70 mb-6">Preencha os dados abaixo para entrar na triagem de novos pilotos. A categoria exibida neste formulário é gerenciada pela equipe do campeonato.</p>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-white/10 rounded-xl p-3">
                        <div class="text-xs text-white/50">Categorias abertas</div>
                        <div class="text-xl font-bold mt-0.5">{{ $categories->count() }}</div>
                    </div>
                    <div class="bg-white/10 rounded-xl p-3">
                        <div class="text-xs text-white/50">Canal de contato</div>
                        <div class="text-xl font-bold mt-0.5">WhatsApp</div>
                    </div>
                </div>
            </div>

            {{-- Form card --}}
            <div class="lg:col-span-2 card p-4 space-y-5">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="section-title mb-0">Formulário de inscrição</div>
                        <div class="text-sm text-ksa-muted mt-0.5">Todos os campos são obrigatórios.</div>
                    </div>
                    <a href="{{ route('public.home') }}" class="btn-outline btn-sm">Voltar</a>
                </div>

                <x-flash />

                @if ($categories->isEmpty())
                    <div class="alert-warning">
                        Não há categorias de inscrição ativas no momento. Tente novamente mais tarde.
                    </div>
                @else
                    <form method="POST" action="{{ route('public.registrations.store') }}" class="space-y-4">
                        @csrf

                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="field">
                                <label for="full_name" class="form-label">Nome completo</label>
                                <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" class="form-input" required>
                            </div>
                            <div class="field">
                                <label for="whatsapp" class="form-label">WhatsApp</label>
                                <input id="whatsapp" type="text" name="whatsapp" value="{{ old('whatsapp') }}" class="form-input" required>
                            </div>
                            <div class="field">
                                <label for="cpf" class="form-label">CPF</label>
                                <input id="cpf" type="text" name="cpf" value="{{ old('cpf') }}" class="form-input" required>
                            </div>
                            <div class="field">
                                <label for="email" class="form-label">E-mail</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-input" required>
                            </div>
                        </div>

                        <div class="field">
                            <label for="address" class="form-label">Endereço</label>
                            <textarea id="address" name="address" class="form-textarea" rows="2" required>{{ old('address') }}</textarea>
                        </div>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="field">
                                <label class="form-label">Já andou de kart antes?</label>
                                <div class="flex gap-4 pt-1">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="has_kart_experience" value="1" @checked(old('has_kart_experience') === '1') required>
                                        <span class="text-sm font-semibold">Sim</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="has_kart_experience" value="0" @checked(old('has_kart_experience') === '0') required>
                                        <span class="text-sm font-semibold">Não</span>
                                    </label>
                                </div>
                            </div>
                            <div class="field">
                                <label class="form-label">Já disputou algum campeonato?</label>
                                <div class="flex gap-4 pt-1">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="has_championship_experience" value="1" @checked(old('has_championship_experience') === '1') required>
                                        <span class="text-sm font-semibold">Sim</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="has_championship_experience" value="0" @checked(old('has_championship_experience') === '0') required>
                                        <span class="text-sm font-semibold">Não</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div class="field">
                                <label for="weight_kg" class="form-label">Peso (kg)</label>
                                <input id="weight_kg" type="text" name="weight_kg" value="{{ old('weight_kg') }}" class="form-input" required>
                            </div>
                            <div class="field">
                                <label for="age" class="form-label">Idade</label>
                                <input id="age" type="number" min="1" name="age" value="{{ old('age') }}" class="form-input" required>
                            </div>
                            <div class="field">
                                <label for="pilot_registration_category_id" class="form-label">Categoria</label>
                                <select id="pilot_registration_category_id" name="pilot_registration_category_id" class="form-select" required>
                                    <option value="">Selecione</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected((string) old('pilot_registration_category_id') === (string) $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2 border-t border-ksa-border">
                            <button type="submit" class="btn-primary btn-lg" data-submitting-label="Enviando...">Enviar inscrição</button>
                            <a href="{{ route('public.home') }}" class="btn-outline btn-lg">Cancelar</a>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </main>
@endsection
