# Gestão de Cookies

## Visão Geral

O BabeStand utiliza cookies para gestão de sessões e funcionalidades de "Remember Me". Todos os cookies são configurados com flags de segurança apropriadas.

## Cookies Utilizados

### BABESTAND_SESSION

| Atributo | Valor |
|----------|-------|
| **Propósito** | Identificador de sessão PHP |
| **Duração** | Sessão (expira ao fechar browser) |
| **HttpOnly** | Sim |
| **Secure** | Sim |
| **SameSite** | Lax |

### remember_token

| Atributo | Valor |
|----------|-------|
| **Propósito** | Login persistente (Remember Me) |
| **Duração** | 30 dias |
| **HttpOnly** | Sim |
| **Secure** | Sim |
| **SameSite** | Lax |
| **Formato** | Hash SHA-256 (64 caracteres hex) |

### cf_clearance (Cloudflare)

| Atributo | Valor |
|----------|-------|
| **Propósito** | Verificação Cloudflare (proteção DDoS) |
| **Origem** | Cloudflare (não controlado pela aplicação) |

## Flags de Segurança

### HttpOnly

```php
session_set_cookie_params([
    'httponly' => true,
    // ...
]);
```

Impede acesso ao cookie via JavaScript, protegendo contra ataques XSS.

### Secure

```php
session_set_cookie_params([
    'secure' => true,
    // ...
]);
```

Cookie só é enviado em conexões HTTPS.

### SameSite

```php
session_set_cookie_params([
    'samesite' => 'Lax',
    // ...
]);
```

Protege contra ataques CSRF:
- **Strict**: Cookie nunca enviado em requests cross-site
- **Lax**: Enviado apenas em navegação top-level GET
- **None**: Sempre enviado (requer Secure)

## Implementação

### Configuração de Sessão (Session.php)

```php
public static function start(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        session_name('BABESTAND_SESSION');
        session_start();
    }
}
```

### Remember Token (Auth.php)

```php
private function setRememberToken(int $userId): void
{
    $token = bin2hex(random_bytes(32));
    $hashedToken = hash('sha256', $token);
    $expires = date('Y-m-d H:i:s', time() + (REMEMBER_ME_DAYS * 86400));
    
    // Guardar hash na BD
    $this->db->query(
        "UPDATE users SET remember_token = ?, remember_token_expires = ? WHERE id = ?",
        [$hashedToken, $expires, $userId]
    );
    
    // Enviar token original ao browser
    setcookie('remember_token', $token, [
        'expires' => time() + (REMEMBER_ME_DAYS * 86400),
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}
```

## Regeneração de Sessão

Para prevenir session fixation, o ID de sessão é regenerado após login:

```php
Session::regenerate(); // Chamado após autenticação bem-sucedida
```

## Evidência

![Cookies no DevTools](screenshots/cookies-devtools.png)

## Boas Práticas Implementadas

| Prática | Implementação |
|---------|---------------|
| ✅ HttpOnly em todos os cookies | Previne acesso JavaScript |
| ✅ Secure flag | Apenas HTTPS |
| ✅ SameSite Lax | Proteção CSRF |
| ✅ Regeneração de sessão | Previne session fixation |
| ✅ Token hash na BD | Token original só no cookie |
| ✅ Expiração configurável | 30 dias para remember me |

## Referências

- [OWASP Session Management](https://owasp.org/www-project-web-security-testing-guide/latest/4-Web_Application_Security_Testing/06-Session_Management_Testing/02-Testing_for_Cookies_Attributes)
- [MDN - HTTP Cookies](https://developer.mozilla.org/en-US/docs/Web/HTTP/Cookies)
- [PHP Session Security](https://www.php.net/manual/en/session.security.php)
