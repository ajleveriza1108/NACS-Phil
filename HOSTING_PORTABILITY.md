# NACS-Phil Hosting Portability Note

The Phase 53-55 release is intentionally tunnel/provider-neutral.

- No ngrok hostname is written into source.
- Public routes use Laravel route generation.
- Dictionary and grammar endpoints are environment-configurable.
- PDF generation is server-side PHP and does not depend on a tunnel.
- Header settings remain application data through `SchoolSetting`.
- Production deployment continues to use the existing NACS-Phil host/readiness gates.

A temporary tunnel may expose the locally running Laravel server, but moving later to another Laravel-capable host should require environment/deployment configuration rather than feature rewrites.
