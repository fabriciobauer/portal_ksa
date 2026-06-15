@extends('layouts.public')

@section('title', 'Inscrição de pilotos')
@section('body_class', 'public-home')

@section('content')
    @php
        $adminUrl = auth()->check() ? route('dashboard') : route('login');
        $adminLabel = auth()->check() ? 'Ir para o painel' : 'Login da administração';
    @endphp

    <div class="public-shell">
        <header class="public-hero">
            <div class="container py-4 py-lg-5">
                <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
                    <a href="{{ route('public.home') }}" class="hero-domain text-decoration-none">ksaracing.com.br</a>
                    <a href="{{ $adminUrl }}" class="btn btn-outline-light px-4">{{ $adminLabel }}</a>
                </div>

                <div class="row g-4 align-items-start">
                    <div class="col-lg-4">
                        <div class="hero-sidecard h-100">
                            <div class="section-eyebrow">Inscrição pública</div>
                            <h1 class="hero-side-title">Seu primeiro passo para correr com a KSA</h1>
                            <p class="hero-description mb-4">
                                Preencha os dados abaixo para entrar na triagem de novos pilotos. A categoria exibida neste formulário é gerenciada pela equipe do campeonato.
                            </p>

                            <div class="hero-stat-grid">
                                <div class="hero-stat-card">
                                    <span class="hero-stat-label">Categorias abertas</span>
                                    <strong>{{ $categories->count() }}</strong>
                                </div>
                                <div class="hero-stat-card">
                                    <span class="hero-stat-label">Canal de contato</span>
                                    <strong>WhatsApp</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="public-form-card">
                            <div class="card-header-public">
                                <div>
                                    <div class="card-title-public">Formulário de inscrição</div>
                                    <div class="card-subtitle-public">
                                        Todos os campos abaixo são obrigatórios.
                                    </div>
                                </div>
                                <a href="{{ route('public.home') }}" class="btn btn-outline-secondary btn-sm">Voltar</a>
                            </div>

                            <div class="p-4">
                                <x-flash />

                                @if ($categories->isEmpty())
                                    <div class="alert alert-warning mb-0">
                                        Não há categorias de inscrição ativas no momento. Tente novamente mais tarde.
                                    </div>
                                @else
                                    <form method="POST"
                                          action="{{ route('public.registrations.store') }}"
                                          class="row g-3"
                                          data-ga-submit-event="registration_submit"
                                          data-ga-submit-params='@json(["page_type" => "public_registration", "section" => "form", "source" => \App\Support\Analytics::source()])'>
                                        @csrf

                                        <div class="col-md-6">
                                            <label for="full_name" class="form-label">Nome completo</label>
                                            <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" class="form-control" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="whatsapp" class="form-label">WhatsApp</label>
                                            <input id="whatsapp" type="text" name="whatsapp" value="{{ old('whatsapp') }}" class="form-control" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="cpf" class="form-label">CPF</label>
                                            <input id="cpf" type="text" name="cpf" value="{{ old('cpf') }}" class="form-control" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="email" class="form-label">E-mail</label>
                                            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                                        </div>

                                        <div class="col-12">
                                            <label for="address" class="form-label">Endereço</label>
                                            <textarea id="address" name="address" class="form-control" rows="2" required>{{ old('address') }}</textarea>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label d-block">Já andou de kart antes?</label>
                                            <div class="d-flex gap-3 pt-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="has_kart_experience" id="has_kart_experience_yes" value="1" @checked(old('has_kart_experience') === '1') required>
                                                    <label class="form-check-label" for="has_kart_experience_yes">Sim</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="has_kart_experience" id="has_kart_experience_no" value="0" @checked(old('has_kart_experience') === '0') required>
                                                    <label class="form-check-label" for="has_kart_experience_no">Não</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label d-block">Já disputou algum campeonato?</label>
                                            <div class="d-flex gap-3 pt-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="has_championship_experience" id="has_championship_experience_yes" value="1" @checked(old('has_championship_experience') === '1') required>
                                                    <label class="form-check-label" for="has_championship_experience_yes">Sim</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="has_championship_experience" id="has_championship_experience_no" value="0" @checked(old('has_championship_experience') === '0') required>
                                                    <label class="form-check-label" for="has_championship_experience_no">Não</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="weight_kg" class="form-label">Qual seu peso?</label>
                                            <input id="weight_kg" type="text" name="weight_kg" value="{{ old('weight_kg') }}" class="form-control" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="age" class="form-label">Qual sua idade?</label>
                                            <input id="age" type="number" min="1" name="age" value="{{ old('age') }}" class="form-control" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="pilot_registration_category_id" class="form-label">Qual categoria você vai disputar?</label>
                                            <select id="pilot_registration_category_id" name="pilot_registration_category_id" class="form-select" required>
                                                <option value="">Selecione</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}" @selected((string) old('pilot_registration_category_id') === (string) $category->id)>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                                            <button type="submit" class="btn btn-warning btn-lg px-4">Enviar inscrição</button>
                                            <a href="{{ route('public.home') }}" class="btn btn-outline-secondary btn-lg px-4">Cancelar</a>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    </div>
@endsection
