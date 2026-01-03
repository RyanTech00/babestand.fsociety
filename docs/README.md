# 📚 Documentação BabeStand

Esta pasta contém a documentação técnica completa do sistema BabeStand.

## 📁 Estrutura

```
docs/
├── arquitetura/          # Diagramas e estrutura do sistema
│   ├── README.md         # Visão geral da arquitetura
│   ├── diagrama-er.md    # Diagrama Entidade-Relacionamento
│   └── fluxos.md         # Fluxos de autenticação e negociação
│
├── seguranca/            # Documentação de segurança
│   ├── README.md         # Índice de segurança
│   ├── headers-http.md   # Headers de segurança HTTP
│   ├── autenticacao.md   # Sistema de autenticação 2FA
│   └── tls.md            # Configuração TLS/SSL
│
├── funcionalidades/      # Descrição das funcionalidades
│   ├── README.md         # Índice de funcionalidades
│   ├── veiculos.md       # Gestão de veículos
│   ├── test-drives.md    # Sistema de test drives
│   ├── vendas.md         # Sistema de vendas
│   └── pos-venda.md      # Área pós-venda
│
└── validacao/            # Evidências de testes
    ├── README.md         # Índice de validação
    ├── screenshots/      # Capturas de ecrã
    └── videos/           # Vídeos de demonstração
```

## 🔗 Links Rápidos

- [🏗️ Arquitetura do Sistema](arquitetura/)
- [🔒 Segurança](seguranca/)
- [⚙️ Funcionalidades](funcionalidades/)
- [✅ Validação e Testes](validacao/)
- [🗄️ Base de Dados](../database/)

## 📖 Como Navegar

Cada pasta contém um `README.md` que serve como índice para os documentos dessa secção. As imagens e vídeos de suporte estão organizados nas respetivas pastas.

## 📝 Convenções

- **Markdown**: Toda a documentação está em formato Markdown
- **Mermaid**: Diagramas renderizados nativamente pelo GitHub
- **Imagens**: Formato PNG, organizadas por funcionalidade
- **Vídeos**: Formato MP4, máximo 100MB por ficheiro
