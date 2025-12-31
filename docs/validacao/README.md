# ✅ Validação de Funcionalidades

Documentação detalhada de cada funcionalidade do sistema BabeStand, com screenshots e explicações passo a passo.

---

## 🔒 Segurança e Autenticação

| Funcionalidade | Descrição | Status |
|----------------|-----------|--------|
| [Conta Bloqueada](conta-bloqueada/) | Proteção contra força bruta + desbloqueio | ✅ Documentado |
| [Recuperar Password](recuperar-password/) | Fluxo de recuperação via email | ✅ Documentado |
| Login com 2FA Email | Autenticação com código por email | 📋 Pendente |
| Login com 2FA TOTP | Autenticação com Google Authenticator | 📋 Pendente |
| Registo de Conta | Criação de nova conta | 📋 Pendente |
| Verificação de Email | Confirmação de email após registo | 📋 Pendente |

---

## 🚗 Veículos

| Funcionalidade | Descrição | Status |
|----------------|-----------|--------|
| Catálogo | Listagem com filtros avançados | 📋 Pendente |
| Detalhe do Veículo | Página individual com galeria | 📋 Pendente |
| Comparador | Comparação lado a lado | 📋 Pendente |
| Favoritos | Sistema de favoritos | 📋 Pendente |
| Lista de Espera | Notificação de disponibilidade | 📋 Pendente |

---

## 📅 Test Drives

| Funcionalidade | Descrição | Status |
|----------------|-----------|--------|
| Agendamento | Calendário interativo | 📋 Pendente |
| Confirmação | Email de confirmação | 📋 Pendente |
| Gestão Admin | Painel de gestão | 📋 Pendente |

---

## 💰 Vendas e Negociação

| Funcionalidade | Descrição | Status |
|----------------|-----------|--------|
| Registo de Venda | Associar comprador a veículo | 📋 Pendente |
| Sistema de Negociação | Propostas e contrapropostas | 📋 Pendente |

---

## 👤 Área do Cliente

| Funcionalidade | Descrição | Status |
|----------------|-----------|--------|
| Perfil | Dados pessoais e avatar | 📋 Pendente |
| Meus Veículos | Veículos adquiridos | 📋 Pendente |
| Documentos | Upload de documentos | 📋 Pendente |
| Manutenções | Registo de manutenções | 📋 Pendente |

---

## 🛠️ Área Administrativa

| Funcionalidade | Descrição | Status |
|----------------|-----------|--------|
| Dashboard | Estatísticas e gráficos | 📋 Pendente |
| CRUD Veículos | Gestão completa de veículos | 📋 Pendente |
| Gestão Utilizadores | Administração de contas | 📋 Pendente |
| Logs de Segurança | Auditoria de eventos | 📋 Pendente |

---

## 📁 Estrutura

```
validacao/
├── README.md                      # Este ficheiro
├── conta-bloqueada/
│   ├── README.md                  # Documentação do fluxo
│   └── images/                    # Screenshots
├── recuperar-password/
│   ├── README.md                  # Documentação do fluxo
│   └── images/                    # Screenshots
├── login-2fa-email/               # 📋 A criar
├── login-2fa-totp/                # 📋 A criar
├── catalogo/                      # 📋 A criar
└── ...
```

---

## 📝 Como Documentar uma Funcionalidade

Cada funcionalidade deve ter:

1. **README.md** - Documentação narrativa com:
   - Descrição do fluxo
   - Screenshots intercaladas
   - Diagrama do fluxo
   - Medidas de segurança (se aplicável)
   - Ficheiros relacionados

2. **images/** - Pasta com screenshots:
   - Nomenclatura: `01-descricao.png`, `02-descricao.png`, etc.
   - Incluir emails se aplicável
   - Resolução consistente

---

## 🎯 Progresso

- ✅ **2** funcionalidades documentadas
- 📋 **~15** funcionalidades pendentes
- 📊 **~12%** completo
