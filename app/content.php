<?php
declare(strict_types=1);

// Textos do PDF "Textos para Website 2026". Classes, FAQ, notícias e eventos passam para a BD na Fase 2.

$kenpoP = 'O Kenpo é um sistema de artes marciais com origens históricas ligadas ao Chuan-Fa ou "Lei do Punho". Com raízes associadas à tradição do Templo Shaolin e aos exercícios originais desenvolvidos para fortalecer o corpo e a mente dos praticantes, o Kenpo é uma disciplina completa de autodefesa e desenvolvimento pessoal.';
$jujitsuP = 'O Ju-Jitsu é uma arte marcial tradicional japonesa cujo significado se traduz em "arte da flexibilidade" ou "arte da doçura". Esta disciplina baseia-se na aplicação de técnicas eficientes de projeções, alavancas articulares, estrangulamentos e controlo do oponente, permitindo maximizar a eficácia através do aproveitamento da força do adversário.';

$morada1 = 'Rua Almirante Cândido dos Reis, 36-B';
$morada2 = '2135-259 Samora Correia';

return [
    'nome' => 'Associação Desportiva Marcial Samora Correia',
    'morada1' => $morada1,
    'morada2' => $morada2,
    'email' => 'associacaodesportivamarcialsc@gmail.com',
    'telefone' => '[A CONFIRMAR]',
    'facebook' => 'https://facebook.com/admsc19',
    'instagram' => 'https://instagram.com/admsc__',

    'boas_vindas' => [
        'Bem-vindo à Associação Desportiva Marcial Samora Correia (ADMSC). Somos uma escola de artes marciais dedicada ao ensino, desenvolvimento e formação humana através da prática de Kenpo e Ju-Jitsu.',
        'Representantes do Kuroishi Ryu Bujutsu em Portugal, promovemos a saúde, a disciplina, o respeito e a autodefesa para todas as idades num ambiente seguro e familiar.',
    ],

    'destaques' => [
        ['Formação Marcial e Humana', 'Treino técnico aliado ao desenvolvimento de valores éticos, disciplina e autocontrolo.'],
        ['Representação Nacional', 'Escola representativa da linha Kuroishi Ryu Bujutsu em Portugal (Kuroishi Ryu Kenpo e Kuroishi Ryu Ju-Jitsu).'],
        ['Para Todas as Idades', 'Classes estruturadas desde a infância até ao escalão sénior, com acompanhamento adaptado.'],
    ],

    'kenpo_texto' => $kenpoP,
    'jujitsu_texto' => $jujitsuP,
    'classes_kenpo' => ['Infantis', 'Iniciados', 'Juvenis', 'Juniores', 'Seniores'],
    'classes_jujitsu' => ['Sénior'],

    'historia' => [
        'A Associação Desportiva Marcial Samora Correia tem como propósito a promoção e o ensino das artes marciais na comunidade, aliando a preparação física à transmissão de valores morais fundamentais.',
        'Como representantes do Kuroishi Ryu Bujutsu em Portugal, enquadramos a nossa prática em duas linhas marciais de excelência: Kuroishi Ryu Kenpo e Kuroishi Ryu Ju-Jitsu. Atualmente, a nossa escola acolhe cerca de 110 atletas de diversos escalões etários, consolidando um percurso de dedicação, espírito de equipa e compromisso com o bem-estar dos nossos alunos.',
    ],
    'visao' => 'Acreditamos que a prática das artes marciais é um caminho contínuo de autoaperfeiçoamento. O nosso trabalho foca-se na formação integral do cidadão, promovendo:',
    'valores' => [
        ['Respeito e Humildade', 'Valorização dos colegas, treinadores e comunidade.'],
        ['Disciplina e Autocontrolo', 'Desenvolvimento da capacidade de concentração, resiliência e gestão emocional.'],
        ['Saúde e Bem-Estar', 'Promoção da condição física, equilíbrio mental e hábitos de vida saudáveis.'],
    ],

    'percurso' => [
        'Funções' => [
            'Mestre',
            'Treinador de Karate (certificado pelo IPDJ)',
            'Técnico de Exercício Físico (certificado pelo IPDJ)',
            'Diretor Técnico da ADMSC',
            'Representante oficial do Kuroishi Ryu Bujutsu em Portugal',
        ],
        'Graduações' => [
            '6.º Dan de Kenpo e 3.º Dan de Ju-Jitsu, Kuroishi Ryu Bujutsu',
            'Maestro de Ju-Jitsu, Kuroishi Ryu Bujutsu',
            'Shihan (6.º Dan) em Kuroishi Ryu Kenpo',
            'Renshi Yodan (4.º Dan) em Chow Hoon Goshinjitsu / Kenpo Karate Jiu-Jitsu',
            '3.º Dan em Kosho Ryu Kenpo',
            '3.º Dan em TaihoJutsu',
            '1.º Grau de Cinto Negro em Hawaiian Lua (Kaihewalu Lua)',
            'Qualificação técnica em Bastão de Combate e Defesa Pessoal pela Federação Nacional do Jogo do Pau Português',
        ],
        'Formação' => [
            'Técnico de Exercício Físico',
            'Formador Inicial de Treinadores',
            'Instrutor de Defesa Pessoal e Dispositivos de Proteção e Segurança (certificado pelo Ministério da Justiça)',
        ],
        'Percurso' => [
            '2003: início da instrução marcial na Sociedade Filarmónica União Samorense (SFUS)',
            'Desde 2017: liderança do corpo técnico da ADMSC',
        ],
    ],

    'faq' => [
        ['Qual é a idade mínima para começar?', '[A PREENCHER]'],
        ['Preciso de experiência prévia?', '[A PREENCHER]'],
        ['Qual a diferença entre Kenpo e Ju-Jitsu?', 'O Kenpo é um sistema de artes marciais com origens ligadas ao Chuan-Fa e à tradição do Templo Shaolin, uma disciplina completa de autodefesa e desenvolvimento pessoal. O Ju-Jitsu é uma arte marcial tradicional japonesa baseada em projeções, alavancas articulares, estrangulamentos e controlo do oponente, aproveitando a força do adversário.'],
        ['Onde são os treinos?', $morada1 . ', ' . $morada2 . '.'],
        ['Posso fazer uma aula experimental?', 'Visite as nossas instalações e conheça de perto a nossa escola, ou deixe-nos uma mensagem no formulário de contacto. [A PREENCHER — detalhes da aula experimental]'],
        ['Que equipamento preciso?', '[A PREENCHER]'],
        ['Quanto custa a mensalidade e a inscrição?', '[A PREENCHER]'],
        ['Os atletas têm seguro desportivo?', '[A PREENCHER]'],
    ],

    'venha_treinar' => 'Visite as nossas instalações e conheça de perto a nossa escola. O nosso empenho reside em proporcionar um ambiente seguro, profissional e focado no crescimento desportivo e pessoal de cada atleta.',

    'modalidades' => [
        '/modalidades/kenpo' => [
            'nome' => 'Kenpo',
            'foto' => '[FOTO — Kenpo]',
            'resumo' => 'Kenpo na ADMSC, em Samora Correia: disciplina completa de autodefesa e desenvolvimento pessoal, com classes dos Infantis aos Seniores.',
            'texto' => [$kenpoP],
            'beneficios' => [
                ['Para Crianças', 'Promove a disciplina, o autocontrolo, o respeito pelas regras e a coordenação motora fundamental para o crescimento saudável. Fortalece a confiança e o espírito de equipa.'],
                ['Para Adultos', 'Melhora a condição física geral, a força e a flexibilidade. Desenvolve técnicas eficazes de autodefesa, auxiliando igualmente na redução do stresse e no reforço da autoconfiança e resiliência diárias.'],
            ],
            'classes_intro' => 'A oferta formativa de Kenpo está organizada em cinco classes preparadas para responder às exigências de cada etapa de desenvolvimento:',
            'classes' => ['Infantis', 'Iniciados', 'Juvenis', 'Juniores', 'Seniores'],
        ],
        '/modalidades/ju-jitsu' => [
            'nome' => 'Ju-Jitsu',
            'foto' => '[FOTO — Ju-Jitsu]',
            'resumo' => 'Ju-Jitsu na ADMSC, em Samora Correia: arte marcial tradicional japonesa com um estudo abrangente de autodefesa pessoal.',
            'texto' => [
                $jujitsuP,
                'Distingue-se de abordagens desportivas modernas por integrar um estudo abrangente de autodefesa pessoal, incluindo trabalho a mãos vazias e o conhecimento de técnicas tradicionais.',
            ],
            'beneficios' => [
                ['Condicionamento Físico', 'Treino integral que desenvolve equilíbrio, agilidade, coordenação e resistência.'],
                ['Autodefesa Eficaz', 'Aprendizagem de conceitos e técnicas pragmáticas para neutralizar agressões de forma segura.'],
                ['Equilíbrio Mental', 'Exercício focado na gestão do stresse, no foco, na tomada de decisão sob pressão e no fortalecimento da autoestimulação positiva.'],
            ],
            'classes_intro' => null,
            'classes' => ['Ju-Jitsu Sénior'],
        ],
    ],
];
