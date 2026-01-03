# ⚙️ Funcionalidades

Documentação detalhada de cada módulo do sistema.

## 📁 Documentos

| Documento | Descrição |
|-----------|-----------|
| [Veículos](veiculos.md) | Gestão de catálogo |
| [Test Drives](test-drives.md) | Sistema de agendamento |
| [Vendas](vendas.md) | Registo de vendas |
| [Pós-Venda](pos-venda.md) | Área do proprietário |
| [Negociação](negociacao.md) | Sistema de venda/troca |

## 📊 Resumo de Funcionalidades

### Área Pública

| Funcionalidade | Descrição | Ficheiros |
|----------------|-----------|-----------|
| **Catálogo** | Listagem com filtros avançados | `veiculos.php`, `veiculo.php` |
| **Comparador** | Comparação lado a lado | `comparar.php` |
| **Favoritos** | Lista de desejos | `api/favorito.php` |
| **Lista Espera** | Notificação disponibilidade | `api/waiting-list.php` |
| **Test Drives** | Agendamento online | `agendar-test-drive.php` |
| **Contacto** | Formulário de contacto | `contacto.php` |

### Área do Cliente

| Funcionalidade | Descrição | Ficheiros |
|----------------|-----------|-----------|
| **Perfil** | Dados pessoais e avatar | `conta/perfil.php` |
| **Segurança** | Password e 2FA | `conta/seguranca.php` |
| **Meus Veículos** | Veículos adquiridos | `conta/meus-veiculos.php` |
| **Meu Veículo** | Gestão individual | `conta/meu-veiculo.php` |
| **Test Drives** | Histórico de agendamentos | `conta/test-drives.php` |
| **Favoritos** | Lista de favoritos | `conta/favoritos.php` |
| **Mensagens** | Comunicações com o stand | `conta/mensagens.php` |

### Área Administrativa

| Funcionalidade | Descrição | Ficheiros |
|----------------|-----------|-----------|
| **Dashboard** | Estatísticas e gráficos | `admin/dashboard.php` |
| **Veículos** | CRUD completo | `admin/veiculos.php` |
| **Utilizadores** | Gestão de contas | `admin/utilizadores.php` |
| **Test Drives** | Gestão de agendamentos | `admin/test-drives.php` |
| **Vendas** | Registo e negociação | `admin/vendas.php` |
| **Mensagens** | Central de comunicações | `admin/mensagens.php` |
| **Reviews** | Moderação de avaliações | `admin/reviews.php` |
| **Marcas** | Gestão de marcas | `admin/marcas.php` |
| **Configurações** | Definições do sistema | `admin/configuracoes.php` |
| **Logs** | Auditoria de segurança | `admin/logs.php` |

## 🔔 Sistema de Notificações

### Notificações In-App

| Evento | Destinatário |
|--------|--------------|
| Test Drive confirmado | Cliente |
| Test Drive cancelado | Cliente |
| Resposta a mensagem | Cliente |
| Favorito vendido | Cliente |
| Novo test drive | Admin |
| Nova mensagem | Admin |

### Notificações Email

| Template | Evento |
|----------|--------|
| `login_token.php` | Login 2FA |
| `verification_email.php` | Verificação de registo |
| `password_reset.php` | Recuperação de password |
| `account_locked.php` | Conta bloqueada |
| `test_drive_confirmation.php` | Confirmação test drive |
| `test_drive_status.php` | Atualização estado |
| `favorite_sold.php` | Favorito vendido |
| `maintenance_reminder.php` | Lembrete manutenção |
| `negotiation_update.php` | Atualização negociação |
| `review_request.php` | Pedido de avaliação |

## ⏰ Tarefas Agendadas (Cron)

| Script | Frequência | Função |
|--------|------------|--------|
| `maintenance-reminders.php` | Diário | Enviar lembretes de manutenção |
| `notify-favorite-sold.php` | Ao vender | Notificar favoritos |
| `notify-waiting-list.php` | Ao disponibilizar | Notificar lista espera |
| `send-review-requests.php` | Diário | Pedir reviews pós-compra |

```cron
# Exemplo crontab
0 9 * * * php /var/www/cron/maintenance-reminders.php
0 10 * * * php /var/www/cron/send-review-requests.php
```
