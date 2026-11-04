#!/usr/bin/env bash
# Thin wrapper that runs the test docker-compose stack with whichever Compose
# CLI is available — `docker compose` (v2 plugin) or the standalone
# `docker-compose` (v1). Always points at tests/docker-compose.yml regardless
# of the caller's current directory.
set -euo pipefail

COMPOSE_FILE="$(cd "$(dirname "$0")/.." && pwd)/docker-compose.yml"

if docker compose version >/dev/null 2>&1; then
  exec docker compose -f "$COMPOSE_FILE" "$@"
elif command -v docker-compose >/dev/null 2>&1; then
  exec docker-compose -f "$COMPOSE_FILE" "$@"
else
  echo "error: neither 'docker compose' (v2 plugin) nor 'docker-compose' (v1) is installed." >&2
  echo "       Install Docker Desktop, or run: brew install docker-compose" >&2
  exit 1
fi
