<?php

namespace App\Support;

class ChampionshipSettings
{
    public const DEFAULTS = [
        'championship.name' => [
            'group_name' => 'campeonato',
            'label' => 'Nome do campeonato',
            'type' => 'string',
            'value' => 'KSA Kart Championship',
            'description' => 'Nome exibido no sistema e relatórios.',
        ],
        'championship.organization' => [
            'group_name' => 'campeonato',
            'label' => 'Organização',
            'type' => 'string',
            'value' => 'KSA',
            'description' => 'Nome da organização promotora.',
        ],
        'karts.range_start' => [
            'group_name' => 'karts',
            'label' => 'Faixa inicial dos karts',
            'type' => 'integer',
            'value' => 1,
            'description' => 'Menor número de kart disponível para sorteio.',
        ],
        'karts.range_end' => [
            'group_name' => 'karts',
            'label' => 'Faixa final dos karts',
            'type' => 'integer',
            'value' => 15,
            'description' => 'Maior número de kart disponível para sorteio.',
        ],
        'scoring.points_table' => [
            'group_name' => 'pontuacao',
            'label' => 'Tabela de pontuação',
            'type' => 'json',
            'value' => [
                '1' => 15,
                '2' => 13,
                '3' => 12,
                '4' => 11,
                '5' => 10,
                '6' => 9,
                '7' => 8,
                '8' => 7,
                '9' => 6,
                '10' => 5,
                '11' => 4,
                '12' => 3,
            ],
            'description' => 'Pontuação base por posição de chegada em cada bateria.',
        ],
        'scoring.discard_count' => [
            'group_name' => 'pontuacao',
            'label' => 'Quantidade de descartes',
            'type' => 'integer',
            'value' => 1,
            'description' => 'Quantidade de etapas descartadas no campeonato.',
        ],
        'scoring.bonus_no_spare_kart_behavior' => [
            'group_name' => 'pontuacao',
            'label' => 'Bônus sem kart reserva',
            'type' => 'string',
            'value' => 'grant',
            'description' => 'Comportamento padrão do bônus quando a exceção "sem kart reserva disponível" estiver marcada.',
        ],
        'stage.default_briefing_time' => [
            'group_name' => 'etapa',
            'label' => 'Horário padrão do briefing',
            'type' => 'string',
            'value' => '08:00',
            'description' => 'Horário sugerido para novas etapas.',
        ],
        'stage.weigh_in_tolerance' => [
            'group_name' => 'etapa',
            'label' => 'Tolerância de pesagem',
            'type' => 'float',
            'value' => 3.00,
            'description' => 'Tolerância da pesagem conjunta kart + piloto (regulamento: 3 kg). Para pesagem individual do piloto, usar 0.',
        ],
        'stage.allow_single_entries' => [
            'group_name' => 'etapa',
            'label' => 'Permitir inscrições avulsas',
            'type' => 'boolean',
            'value' => true,
            'description' => 'Habilita inscrições avulsas nas categorias.',
        ],
        'standings.championship_tiebreak_scope' => [
            'group_name' => 'classificacao',
            'label' => 'Escopo do desempate do campeonato',
            'type' => 'string',
            'value' => 'all_heats',
            'description' => 'Define se o contador de posições do desempate considera todas as baterias da temporada ou apenas as etapas válidas.',
        ],
        'standings.ambiguity_behavior' => [
            'group_name' => 'classificacao',
            'label' => 'Ambiguidades do regulamento',
            'type' => 'string',
            'value' => 'manual',
            'description' => 'Comportamento padrão quando o regulamento exigir decisão administrativa.',
        ],
        'stage.briefing_adjustment_mode' => [
            'group_name' => 'etapa',
            'label' => 'Registro de briefing',
            'type' => 'string',
            'value' => 'manual_penalty_positions',
            'description' => 'Campo informativo para registrar observações de briefing sem alterar o grid automaticamente.',
        ],
    ];
}
