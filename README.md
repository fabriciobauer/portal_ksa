# KSA Kart Championship

Sistema Laravel para gestao do campeonato KSA Racing.

## Docker / producao

O container sobe usando o `.env` informado no `docker compose`, sem rodar `migrate` ou `seed` automaticamente.

Subir:

```bash
docker compose up -d --build
```

Comportamento padrao:

- usa o `.env` atual via `env_file`
- cria `storage:link` se necessario
- nao roda migrations automaticamente
- nao roda seeders automaticamente
- quando `APP_ENV=production`, executa `php artisan optimize` antes de iniciar

Flags opcionais no `.env`:

```env
CONTAINER_RUNTIME=serve
CONTAINER_HOST=0.0.0.0
CONTAINER_RUN_MIGRATIONS=false
CONTAINER_RUN_SEEDERS=false
CONTAINER_RUN_STORAGE_LINK=true
CONTAINER_OPTIMIZE=auto
```

Para uma subida controlada com migration:

```env
CONTAINER_RUN_MIGRATIONS=true
CONTAINER_RUN_SEEDERS=false
```

Depois da aplicacao, volte essas flags para `false`.

## GA4

O Google Analytics 4 e configurado por ambiente e carregado de forma centralizada nos layouts publico, administrativo e de login.

Variaveis:

```env
GA4_ENABLED=true
GA4_MEASUREMENT_ID=G-W23LY5HJBT
GA4_SOURCE=website
```

Arquivo de configuracao:

- `config/analytics.php`

O snippet e injetado por:

- `resources/views/partials/analytics.blade.php`

Eventos implementados sem envio de PII:

- `home_inscreva_se_click`
- `registration_form_view`
- `registration_submit`
- `registration_submit_success`
- `admin_registrations_view`
- `registration_payment_marked`
- `registration_payment_unmarked`
- `registration_converted_to_pilot`

## Fluxo de inscricao de pilotos

1. A home publica exibe o botao `Inscreva-se`.
2. O formulario publico fica em `GET /inscreva-se`.
3. O envio publico acontece em `POST /inscricoes`.
4. A gestao administrativa fica em `GET /admin/inscricoes`.
5. As categorias do formulario ficam em `GET /admin/inscricoes/categorias`.
6. Ao marcar uma inscricao como paga, o sistema cria ou atualiza um piloto existente com matching por CPF, e-mail, WhatsApp e nome completo.
7. O piloto sincronizado aparece normalmente no cadastro de pilotos, sem inscricao automatica em temporada/categoria esportiva.
