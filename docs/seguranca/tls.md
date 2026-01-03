# Configuração TLS/SSL

## Visão Geral

O BabeStand utiliza HTTPS com TLS 1.3, o protocolo de segurança mais recente, garantindo a confidencialidade e integridade das comunicações.

## Configuração Atual

| Parâmetro | Valor |
|-----------|-------|
| **Protocolo** | TLS 1.3 |
| **Cipher Suite** | TLS_AES_128_GCM_SHA256 |
| **Key Exchange** | X25519 (Curve25519) |
| **Assinatura** | RSA-PSS-SHA256 |

## Certificado

| Campo | Valor |
|-------|-------|
| **Emitido para** | fsociety.pt |
| **Emitido por** | Let's Encrypt (R12) |
| **Válido desde** | 01 Dez 2025 |
| **Válido até** | 01 Mar 2026 |
| **Fingerprint SHA-256** | 4C:C4:9E:BD:7F:59:E9:E4:95:2B... |

## Características de Segurança

### TLS 1.3

O TLS 1.3 oferece várias melhorias sobre versões anteriores:

- **Handshake mais rápido**: 1-RTT em vez de 2-RTT
- **Forward Secrecy obrigatório**: Todas as cipher suites usam Diffie-Hellman efémero
- **Cipher suites simplificadas**: Apenas algoritmos modernos e seguros
- **0-RTT opcional**: Resumption com zero round trips

### X25519

O algoritmo X25519 para troca de chaves oferece:
- Segurança de 128 bits
- Resistência a timing attacks
- Performance superior a curvas NIST tradicionais

### AES-128-GCM

O modo GCM (Galois/Counter Mode) proporciona:
- Encriptação autenticada (AEAD)
- Proteção de integridade
- Paralelização eficiente

## Configuração do Servidor

### Apache (exemplo)

```apache
SSLProtocol -all +TLSv1.3 +TLSv1.2
SSLCipherSuite TLS_AES_128_GCM_SHA256:TLS_AES_256_GCM_SHA384:TLS_CHACHA20_POLY1305_SHA256
SSLHonorCipherOrder on
```

## Let's Encrypt

O certificado é emitido pela Let's Encrypt, uma autoridade de certificação gratuita e automatizada.

### Renovação Automática

```bash
# Certbot renova automaticamente via cron
0 0,12 * * * certbot renew --quiet
```

## Certificate Transparency

O certificado está registado em logs de Certificate Transparency (CT), permitindo auditoria pública.

- **SCTs**: Registos de SCT válidos ✓

## Evidência

![TLS Security no DevTools](screenshots/tls-security.png)

## Verificação Externa

### SSL Labs

Para verificar a configuração TLS:
- [SSL Labs Test](https://www.ssllabs.com/ssltest/analyze.html?d=babestand.fsociety.pt)

### Verificação Manual

```bash
# Verificar certificado
openssl s_client -connect babestand.fsociety.pt:443 -tls1_3

# Ver detalhes do certificado
echo | openssl s_client -connect babestand.fsociety.pt:443 2>/dev/null | openssl x509 -noout -text
```

## Referências

- [Let's Encrypt Documentation](https://letsencrypt.org/docs/)
- [Mozilla SSL Configuration Generator](https://ssl-config.mozilla.org/)
- [TLS 1.3 RFC 8446](https://tools.ietf.org/html/rfc8446)
