# NACS-Phil Phase 57 - Local Reverse Proxy HTTPS Preview Readiness

Phase 57 fixes public-site asset generation when NACS-Phil is temporarily
exposed through a locally-running reverse proxy or HTTPS tunnel.

The public layout uses Laravel `asset()` helpers. Without trusted-proxy
configuration, Laravel sees the tunnel-to-local connection as HTTP and may
generate `http://` stylesheet/script URLs even though the browser is visiting
the public site over HTTPS. Browsers block those active mixed-content assets,
which produces an unstyled page.

`bootstrap/app.php` now trusts standard X-Forwarded headers only when the
immediate proxy connection is local loopback:

- 127.0.0.1
- ::1

Trusted headers:
- X-Forwarded-For
- X-Forwarded-Host
- X-Forwarded-Port
- X-Forwarded-Proto

No temporary ngrok hostname, token, or provider-specific identifier is written
to source. A future production host using a non-loopback load balancer should
configure that provider's documented proxy addresses/ranges deliberately.
