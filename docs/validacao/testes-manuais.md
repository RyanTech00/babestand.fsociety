# 🧪 Testes Manuais de Segurança

Esta secção documenta os testes manuais realizados para validar as proteções de segurança implementadas.

## Metodologia

Os testes foram realizados seguindo o [OWASP Testing Guide](https://owasp.org/www-project-web-security-testing-guide/), utilizando a ferramenta [Caido](https://caido.io/) para interceptar e manipular pedidos HTTP.

## Testes Realizados

### 1. SQL Injection

**Objetivo**: Verificar proteção contra injeção de SQL

**Payload testado**:
```sql
' OR '1'='1
SELECT * FROM Users WHERE UserId = 105 OR 1=1
```

**Resultado**: ✅ **PROTEGIDO**
- Frontend valida formato de email
- Backend usa prepared statements (PDO)
- Payload armazenado como texto literal

![SQL Injection Test](screenshots/sqli-test.png)

---

### 2. Cross-Site Scripting (XSS)

**Objetivo**: Verificar escape de output

**Payloads testados**:
```html
<script>alert('XSS')</script>
<img src=x onerror=alert(1)>
```

**Resultado**: ✅ **PROTEGIDO**
- `htmlspecialchars()` no backend
- `escapeHtml()` no frontend
- CSP headers ativos

![XSS Test](screenshots/xss-test.png)

---

### 3. Cross-Site Request Forgery (CSRF)

**Objetivo**: Verificar validação de tokens CSRF

**Teste**: Modificar token CSRF no pedido POST

**Resultado**: ✅ **PROTEGIDO**
- Todos os formulários têm `_csrf_token`
- Token validado com `hash_equals()`
- Mensagem: "Token de segurança inválido."

![CSRF Test](screenshots/csrf-test.png)

---

### 4. Insecure Direct Object Reference (IDOR)

**Objetivo**: Verificar validação de propriedade

**Teste**: Aceder a veículo/documento de outro utilizador

**Resultado**: ✅ **PROTEGIDO**
- Validação de `buyer_id` / `user_id`
- Mensagem: "Veículo não encontrado ou não lhe pertence."

---

### 5. Upload de Ficheiros Maliciosos

**Objetivo**: Verificar validação de uploads

**Ficheiros testados**:
- `shell.php` - PHP direto
- `shell.php.jpg` - Dupla extensão
- `test.pdf` com `<?php system($_GET['cmd']); ?>`

**Resultado**: ✅ **PROTEGIDO**
- Validação de extensão, MIME, magic bytes
- Scan de padrões maliciosos
- Regeneração de imagens (remove código embebido)

![Upload Test](screenshots/upload-malicioso.png)

---

### 6. Brute Force

**Objetivo**: Verificar rate limiting

**Teste**: 5+ tentativas de login com password errada

**Resultado**: ✅ **PROTEGIDO**
- Bloqueio após 5 tentativas
- Duração: 30 minutos
- Email de alerta enviado
- Bloqueio por conta (não por IP)

![Brute Force Test](screenshots/brute-force.png)

---

### 7. Session Hijacking

**Objetivo**: Verificar configuração de cookies

**Verificações**:
- `HttpOnly`: ✅ Ativo
- `Secure`: ✅ Ativo
- `SameSite`: ✅ Lax

---

### 8. Open Redirect

**Objetivo**: Verificar validação de URLs de redirect

**Payload**: `?redirect=https://evil.com`

**Resultado**: ✅ **PROTEGIDO**
- Função `isValidRedirectUrl()` valida:
  - Começa com `/`
  - Não contém `//`
  - Sem caracteres especiais

---

## Resumo dos Resultados

| Vulnerabilidade | Resultado | Proteção |
|-----------------|-----------|----------|
| SQL Injection | ✅ Protegido | Prepared Statements |
| XSS Stored | ✅ Protegido | htmlspecialchars() |
| XSS Reflected | ✅ Protegido | Output encoding |
| CSRF | ✅ Protegido | Tokens únicos |
| IDOR | ✅ Protegido | Validação de propriedade |
| Upload Malicioso | ✅ Protegido | 9 camadas de validação |
| Brute Force | ✅ Protegido | Rate limiting |
| Session Hijacking | ✅ Protegido | Cookie flags |
| Open Redirect | ✅ Protegido | URL validation |

## Ferramenta Utilizada

**Caido** - https://caido.io/
- Proxy HTTP/HTTPS
- Intercepção de pedidos
- Modificação de parâmetros
- Replay de pedidos
