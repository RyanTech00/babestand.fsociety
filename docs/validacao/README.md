# ✅ Validação e Testes

Documentação dos testes de segurança e validação de funcionalidades do sistema BabeStand.

---

## 🧪 Testes de Segurança

| Documento | Descrição |
|-----------|-----------|
| [Testes Manuais](testes-manuais.md) | SQL Injection, XSS, CSRF, Upload, Brute Force |
| [Testes Automatizados](testes-automatizados.md) | Cloudflare WAF, CrowdSec IDS, DDoS, TLS |

### Resumo de Resultados

| Severidade | Vulnerabilidades |
|------------|------------------|
| 🔴 Crítica | 0 |
| 🟠 Alta | 0 |
| 🟡 Média | 1 |
| 🟢 Baixa | 2 |
| **Total** | **3** (configuração opcional) |

### Proteções Validadas

| Vulnerabilidade | Estado | Mecanismo |
|-----------------|--------|-----------|
| SQL Injection | ✅ Protegido | Prepared Statements |
| XSS Stored | ✅ Protegido | htmlspecialchars() |
| CSRF | ✅ Protegido | Tokens únicos |
| Upload Malicioso | ✅ Protegido | 9 camadas |
| Brute Force | ✅ Protegido | Rate limiting |
| DDoS | ✅ Protegido | Cloudflare WAF |

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
├── testes-manuais.md             # Testes com Caido
├── testes-automatizados.md       # Ferramentas Kali Linux
├── screenshots/                   # Screenshots de testes
│   ├── admin-dashboard.png
│   ├── admin-veiculos.png
│   ├── admin-test-drives.png
│   ├── admin-reviews.png
│   ├── admin-logs-seguranca.png
│   ├── veiculo-detalhe.png
│   ├── csrf-token.png
│   ├── http-headers.png
│   ├── cookies-devtools.png
│   ├── tls-security.png
│   ├── crowdsec-servers.png
│   ├── crowdsec-alerts.png
│   └── upload-malicioso-*.png
├── conta-bloqueada/
│   ├── README.md
│   └── images/
└── recuperar-password/
    ├── README.md
    └── images/
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

- ✅ **4** documentos de testes
- ✅ **15+** screenshots de validação
- ✅ **Testes manuais** completos
- ✅ **Testes automatizados** completos
- ✅ **Defesa em profundidade** validada
