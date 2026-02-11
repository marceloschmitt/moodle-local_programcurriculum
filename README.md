# Local Program Curriculum

Plugin **local** do Moodle que adiciona o item "Currículo" à navegação principal do curso (mesmo nível que Participantes, Notas, Competências), em vez do menu "More".

## O que faz

- Usa o callback `local_programcurriculum_extend_navigation_course()` para inserir o link "Currículo" no nó do curso (mesmo nível que Participantes, Notas).
- O link aponta para `/blocks/programcurriculum/view.php`, que faz o roteamento correto (progresso próprio ou lista de alunos, conforme permissões).
- Verifica a capability `block/programcurriculum:viewownprogress` antes de exibir o item.

## Dependência

**Requer** o plugin `block_programcurriculum` instalado. Este plugin local apenas adiciona o link na navegação; a lógica, páginas e funcionalidades ficam no block.

## Instalação

1. Instale primeiro o `block_programcurriculum`.
2. Copie esta pasta para `local/programcurriculum` no Moodle.
3. Acesse Administração do site → Notificações para concluir o upgrade.

## Estrutura

```
local/programcurriculum/
├── db/access.php      # Sem capabilities (usa as do block)
├── lang/
│   ├── en/
│   └── pt_br/
├── lib.php            # extend_navigation
├── README.md
└── version.php
```

## Desenvolvimento

- Plugin do tipo `local`.
- Não define capabilities próprias.
- Usa strings em `local_programcurriculum` (curriculumnav, pluginname).
