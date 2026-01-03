# Diagramas de Fluxo

## Fluxo de Autenticação

```mermaid
flowchart TD
    A[Submeter Credenciais] --> B{Rate Limit?}
    B -->|Sim| C[Erro: Aguarde]
    B -->|Não| D{Credenciais OK?}
    D -->|Não| E[Erro: Inválidas]
    D -->|Sim| F{Conta Ativa?}
    F -->|Não| G[Erro: Suspensa]
    F -->|Sim| H{Conta Bloqueada?}
    H -->|Sim| I[Erro: Aguarde X min]
    H -->|Não| J{TOTP Ativo?}
    J -->|Sim| K[Formulário TOTP]
    J -->|Não| L[Enviar Email 2FA]
    K --> M{Código OK?}
    L --> N{Link/Código OK?}
    M -->|Não| O[Erro: Código Inválido]
    M -->|Sim| P{Remember Me?}
    N -->|Não| O
    N -->|Sim| P
    P -->|Sim| Q[Set Cookie 30d]
    P -->|Não| R[Login OK]
    Q --> R

    style A fill:#3182ce
    style C fill:#e53e3e
    style E fill:#e53e3e
    style G fill:#e53e3e
    style I fill:#ed8936
    style O fill:#e53e3e
    style R fill:#38a169
```

## Ciclo de Vida da Sessão

```mermaid
stateDiagram-v2
    [*] --> SemSessao
    SemSessao --> Pendente2FA : Login
    Pendente2FA --> SessaoAtiva : 2FA OK
    Pendente2FA --> SemSessao : Timeout/Falha
    SessaoAtiva --> SessaoAtiva : Atividade
    SessaoAtiva --> RememberMe : + Cookie
    SessaoAtiva --> Expirada : Timeout
    RememberMe --> SessaoAtiva : Retorno
    Expirada --> SemSessao : Logout
    SessaoAtiva --> SemSessao : Logout
```

## Fluxo de Test Drive

```mermaid
flowchart TD
    A[Utilizador seleciona veículo] --> B[Escolhe data/hora]
    B --> C{Slot disponível?}
    C -->|Não| D[Mostrar slots livres]
    C -->|Sim| E[Submeter pedido]
    E --> F[Email para Admin]
    F --> G[Notificação utilizador]
    G --> H{Admin confirma?}
    H -->|Sim| I[Email confirmação]
    H -->|Não| J[Email cancelamento]
    I --> K[Test Drive]
    K --> L{Compareceu?}
    L -->|Sim| M[Estado: Concluído]
    L -->|Não| N[Estado: Não Compareceu]

    style E fill:#3182ce
    style I fill:#38a169
    style J fill:#e53e3e
    style M fill:#38a169
    style N fill:#ed8936
```

## Fluxo de Negociação (Venda/Troca)

```mermaid
stateDiagram-v2
    [*] --> Pendente : Novo Pedido
    Pendente --> EmAnalise : Admin recebe
    EmAnalise --> OfertaEnviada : Admin envia oferta
    OfertaEnviada --> UtilizadorAceitou : Utilizador aceita
    OfertaEnviada --> Contraproposta : Utilizador contrapropõe
    Contraproposta --> OfertaEnviada : Admin nova oferta
    Contraproposta --> Recusada : Admin recusa
    UtilizadorAceitou --> Concluida : Finalizado
    EmAnalise --> Recusada : Admin recusa
    Pendente --> Cancelada : Utilizador cancela

    note right of OfertaEnviada : Email enviado
    note right of Contraproposta : Email enviado
```

## Fluxo de Manutenção (Pós-Venda)

```mermaid
flowchart TD
    A[Veículo Vendido] --> B[Buyer ID associado]
    B --> C[Cálculo próxima manutenção]
    C --> D{Baseado em:}
    D --> E[Intervalos da marca]
    D --> F[Última manutenção]
    E --> G[Criar lembrete]
    F --> G
    G --> H[Cron job diário]
    H --> I{30 dias para manutenção?}
    I -->|Sim| J[Enviar email]
    I -->|Não| K[Aguardar]
    J --> L[Marcar como enviado]

    style A fill:#3182ce
    style J fill:#38a169
```

## Fluxo de Upload de Imagem

```mermaid
flowchart TD
    A[Upload ficheiro] --> B{Tamanho OK?}
    B -->|Não| C[Erro: Muito grande]
    B -->|Sim| D{MIME type válido?}
    D -->|Não| E[Erro: Tipo inválido]
    D -->|Sim| F{Magic bytes OK?}
    F -->|Não| G[Erro: Ficheiro corrompido]
    F -->|Sim| H{getimagesize OK?}
    H -->|Não| I[Erro: Não é imagem]
    H -->|Sim| J[Regenerar com GD]
    J --> K[Remover metadados]
    K --> L[Guardar ficheiro]
    L --> M[Atualizar BD]

    style A fill:#3182ce
    style C fill:#e53e3e
    style E fill:#e53e3e
    style G fill:#e53e3e
    style I fill:#e53e3e
    style M fill:#38a169
```

## Fluxo de Rate Limiting

```mermaid
flowchart TD
    A[Tentativa Login] --> B[Registar em login_attempts]
    B --> C{Contar últimos 30 min}
    C --> D{>= 5 tentativas?}
    D -->|Sim| E[Bloquear IP/Email]
    D -->|Não| F{Credenciais OK?}
    F -->|Não| G[Incrementar contador user]
    F -->|Sim| H[Reset contador]
    G --> I{>= 5 seguidas?}
    I -->|Sim| J[Bloquear conta]
    I -->|Não| K[Continuar]
    J --> L[Enviar email desbloqueio]

    style E fill:#e53e3e
    style J fill:#e53e3e
    style L fill:#ed8936
    style H fill:#38a169
```
