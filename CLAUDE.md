# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**MCPGML** is an enterprise WordPress LMS platform (WordPress + LearnDash) with Model Context Protocol (MCP) integration, deployed on AWS EKS. It exposes LMS functionality as MCP tools so AI agents can interact with WordPress programmatically.

- **Live site:** https://eks12.lmseunoconsulting.com/
- **Kubernetes deployment:** `dp-prueba12` in namespace `plataformas`
- **Database:** MariaDB (`wpprueba12`)
- **PHP:** 8.4

## Mandatory Deployment Workflow

Every task follows this sequence:
1. Edit locally in `wordpress/wp-content/...`
2. Commit with semantic format: `type(scope): description`
3. Sync files to server (using `copy-changed-files.ps1` or `kubectl cp`)
4. Validate on production

## Common Commands

### Kubernetes Operations
```bash
# Apply manifest changes
kubectl apply -f wordpress12.yaml -n plataformas

# Monitor rollout
kubectl rollout status deployment/dp-prueba12 -n plataformas

# View pods/logs
kubectl get pods -n plataformas -l app=wpprueba12
kubectl logs -n plataformas -l app=wpprueba12 -f
```

### WP-CLI (via sidecar container)
```bash
# General WP-CLI pattern
kubectl exec -n plataformas deployment/dp-prueba12 -c wpcli -- wp <command>

# Common operations
kubectl exec -n plataformas deployment/dp-prueba12 -c wpcli -- wp plugin list
kubectl exec -n plataformas deployment/dp-prueba12 -c wpcli -- wp cache flush
kubectl exec -n plataformas deployment/dp-prueba12 -c wpcli -- wp db check
```

## Architecture

### MCP Integration Stack
```
AI Agent (Claude)
    ↓ MCP Protocol
mcp-adapter plugin       ← converts WordPress abilities to MCP tools
    ↓ WordPress hooks
abilities-api plugin     ← standard ability registration API
    ↓ WordPress hooks
lmseu-mcp-abilities      ← EUNO custom abilities (primary development target)
    ↓ WordPress functions
WordPress + LearnDash + MariaDB
```

### Custom Code Locations
- **Primary plugin:** `wordpress/wp-content/plugins/lmseu-mcp-abilities/`
  - `lmseu-mcp-abilities.php` — main plugin file, activation hooks, initialization
  - `includes/` — one class per file, all core logic here
- **Custom theme:** `wordpress/wp-content/themes/eunolms/`
  - `page-mi-perfil.php` — student profile page template
  - `functions.php` — script enqueuing, menu registration

### Key Classes in `lmseu-mcp-abilities/includes/`

| Class | Role |
|-------|------|
| `LMSEU_Ability_Registrar` | Registers custom MCP abilities |
| `LMSEU_Student_Profile` | Profile page & `[euno_student_profile]` shortcode |
| `LMSEU_Enrolled_Courses` | `[euno_enrolled_courses]` shortcode + course display |
| `LMSEU_Reports_Dashboard` | `[euno_reports_dashboard]` shortcode + analytics |
| `LMSEU_Client_Branding_Manager` | Per-client branding (logos, colors) |
| `LMSEU_Client_Storage_Manager` | Persistent storage for client settings |
| `LMSEU_Client_LearnDash_Manager` | LearnDash-specific client operations |
| `LMSEU_LearnDash_Abilities` | MCP abilities for LearnDash data |
| `LMSEU_MCP_HTTP_Auth` | HTTP auth for MCP requests |

### Custom Database Table
`wp_euno_time_tracking` (created on plugin activation): `id, user_id, course_id, step_id, seconds, last_updated`

### Kubernetes Setup
- **WordPress container:** `wordpress:php8.4-apache`, 2–4Gi RAM, 1–2 vCPU
- **WP-CLI sidecar:** `wordpress:cli`, shares volumes with WordPress container, runs `sleep infinity`
- **Storage:** PVC `pvc-client-auteco` (subPath: wpprueba12)
- **HPA:** 1–4 replicas, scales at 80% CPU

## Coding Standards

### PHP
- WordPress Coding Standards: 4-space indentation, no tabs
- Naming: `Class_Name` (PascalCase+underscores), `function_name()` (snake_case), `$variable_name`, `CONSTANT_NAME`
- Prefixes: `euno_` or `lmseu_` on all functions, classes, shortcodes, hooks
- Always use `$wpdb->prepare()` for SQL; sanitize inputs with WordPress sanitization functions
- Use `WP_Error` for error handling, not exceptions
- PHPDoc on all public methods

### CSS/Theme
- BEM-style namespaced classes: `.euno-student-profile__header`

### Git
- Semantic commits: `feat`, `fix`, `refactor`, `docs`, `style`, `test`, `chore`
- Format: `type(scope): description`

## Important Notes

- **No automated CI/CD** — all deployment is manual
- **No build system** — plain PHP/CSS/JS, no webpack or composer in custom code
- **Temporary debug files** (`debug_*.php`, `temp-*.php`) must NOT be committed
- The `.clinerules/` directory contains detailed operational rules and workflows
- MCP abilities are registered via the `wp_abilities_api_init` WordPress hook
- Each ability must define JSON Schema for inputs/outputs and a permission callback
