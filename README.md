<div align="center">

# 🚗 BabeStand

<img width="1952" height="2208" alt="Gemini_Generated_Image_9l0r469l0r469l0r" src="https://github.com/user-attachments/assets/3d084acb-a89a-4240-826f-892d0f8840a6" />

**Sistema de Gestão de Stand Automóvel**

[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)](https://php.net)
[![MariaDB](https://img.shields.io/badge/MariaDB-10.11-003545?logo=mariadb&logoColor=white)](https://mariadb.org)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![TLS](https://img.shields.io/badge/TLS-1.3-success)](https://www.cloudflare.com/learning/ssl/transport-layer-security-tls/)
[![Let's Encrypt](https://img.shields.io/badge/SSL-Let's%20Encrypt-blue)](https://letsencrypt.org)

https://github.com/user-attachments/assets/9ec509fc-3b2d-4fc8-8769-dff54f56539c

> Projeto desenvolvido no âmbito do CTeSP em Cibersegurança, Redes e Sistemas Informáticos - ESTG/IPP

</div>

---

## 📋 Índice

- [Sobre o Projeto](#-sobre-o-projeto)
- [Funcionalidades](#-funcionalidades)
- [Arquitetura](#-arquitetura)
- [Tecnologias](#-tecnologias)
- [Segurança](#-segurança)
- [Documentação](#-documentação)
- [Demonstração](#-demonstração)
- [Autores](#-autores)

## 🎯 Sobre o Projeto

O BabeStand é uma plataforma web completa para gestão de stands automóveis, desenvolvida em PHP puro com arquitetura MVC simplificada. O sistema permite a gestão integral de veículos, utilizadores, test drives, vendas e comunicações.

### Objetivos
- Digitalização completa da operação de um stand automóvel
- Interface intuitiva para clientes e administradores
- Segurança robusta com autenticação de dois fatores
- Sistema de pós-venda para fidelização de clientes

## ✨ Funcionalidades

### Área Pública
| Funcionalidade | Descrição |
|----------------|-----------|
| 🔍 Catálogo | Listagem com filtros avançados (marca, combustível, preço, ano) |
| 📊 Comparador | Comparação lado a lado de veículos |
| 📅 Test Drives | Agendamento online com calendário interativo |
| 📝 Contacto | Formulário com notificações automáticas |
| ⭐ Favoritos | Lista de veículos favoritos |
| 📋 Lista Espera | Notificação quando veículo fica disponível |

### Área do Cliente
| Funcionalidade | Descrição |
|----------------|-----------|
| 👤 Perfil | Gestão de dados pessoais e avatar |
| 🔐 Segurança | 2FA (TOTP/Email), histórico de sessões |
| 🚙 Meus Veículos | Gestão pós-compra (documentos, manutenções) |
| 💬 Negociação | Sistema de venda/troca com contrapropostas |
| ⭐ Reviews | Avaliação de veículos adquiridos |

### Área Administrativa
| Funcionalidade | Descrição |
|----------------|-----------|
| 📊 Dashboard | Estatísticas e gráficos em tempo real |
| 🚗 Veículos | CRUD completo com múltiplas imagens |
| 👥 Utilizadores | Gestão e moderação |
| 📅 Test Drives | Confirmação e gestão de agendamentos |
| 💰 Vendas | Registo e acompanhamento |
| 📧 Mensagens | Central de comunicações |
| 🔒 Logs | Auditoria de segurança |

## 🏗 Arquitetura

```
┌─────────────────────────────────────────────────────────────┐
│                      APRESENTAÇÃO                           │
│            HTML/CSS/JavaScript │ Bootstrap 5                │
├─────────────────────────────────────────────────────────────┤
│                      CONTROLADORES                          │
│              Páginas PHP │ API REST │ Validação             │
├─────────────────────────────────────────────────────────────┤
│                    LÓGICA DE NEGÓCIO                        │
│        Auth │ Validator │ Notification │ CSRF │ Session     │
├─────────────────────────────────────────────────────────────┤
│                     ACESSO A DADOS                          │
│            Database (PDO) │ Cache │ SecurityLogger          │
├─────────────────────────────────────────────────────────────┤
│                      PERSISTÊNCIA                           │
│               MariaDB 10.11 │ Sistema de Ficheiros          │
└─────────────────────────────────────────────────────────────┘
```

📖 [Ver documentação completa da arquitetura](docs/arquitetura/)

## 🛠 Tecnologias

| Camada | Tecnologia | Versão |
|--------|------------|--------|
| **Backend** | PHP | 8.3 |
| **Base de Dados** | MariaDB | 10.11 |
| **Frontend** | Bootstrap | 5.3 |
| **Servidor** | Apache | 2.4 |
| **SSL** | Let's Encrypt | - |
| **Proxy** | Cloudflare | - |

## 🔒 Segurança

O sistema implementa múltiplas camadas de segurança:

| Medida | Implementação |
|--------|---------------|
| **Autenticação** | 2FA (TOTP + Email), Argon2ID |
| **Sessões** | Regeneração de ID, timeout configurável |
| **CSRF** | Tokens em todos os formulários |
| **XSS** | Escape de output, CSP headers |
| **SQL Injection** | Prepared statements (PDO) |
| **Upload** | Validação MIME, magic bytes, regeneração |
| **TLS** | TLS 1.3, HSTS |
| **Headers** | X-Frame-Options, X-Content-Type-Options |

📖 [Ver documentação completa de segurança](docs/seguranca/)

## 📚 Documentação

| Documento | Descrição |
|-----------|-----------|
| [📐 Arquitetura](docs/arquitetura/) | Diagramas e estrutura do sistema |
| [🔒 Segurança](docs/seguranca/) | Medidas de segurança implementadas |
| [⚙️ Funcionalidades](docs/funcionalidades/) | Descrição detalhada de cada módulo |
| [✅ Validação](docs/validacao/) | Screenshots e vídeos de testes |
| [🗄️ Base de Dados](database/) | Schema e diagrama ER |

## 🎬 Demonstração

### Vídeos de Validação

| Funcionalidade | Vídeo |
|----------------|-------|
| Login com 2FA | [▶️ Ver vídeo](docs/validacao/videos/) |
| Gestão de Veículos | [▶️ Ver vídeo](docs/validacao/videos/) |
| Test Drives | [▶️ Ver vídeo](docs/validacao/videos/) |
| Sistema de Vendas | [▶️ Ver vídeo](docs/validacao/videos/) |

### Screenshots

<details>
<summary>📸 Ver screenshots do sistema</summary>

| Página | Screenshot |
|--------|------------|
| Homepage | *Em breve* |
| Catálogo | *Em breve* |
| Dashboard Admin | *Em breve* |

</details>

## 🌐 Ambiente de Produção

- **URL**: [https://babestand.fsociety.pt](https://babestand.fsociety.pt)
- **TLS**: 1.3 (TLS_AES_128_GCM_SHA256)
- **Certificado**: Let's Encrypt (válido até Mar 2026)
- **CDN/Proxy**: Cloudflare

## 👥 Autores

| Nome |
|------|
| Ryan da Silva Barbosa
| Igor Gabriel Macedo Araújo 
| Hugo Danial da Silva Correia

---

**ESTG - Instituto Politécnico do Porto**  
CTeSP em Cibersegurança, Redes e Sistemas Informáticos
2025/2026
