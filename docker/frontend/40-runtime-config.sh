#!/usr/bin/env sh
set -eu
api_origin="${API_ORIGIN:-http://localhost:8000}"
printf 'window.__MINORI_CONFIG__ = { apiOrigin: "%s" };\n' "$api_origin" > /usr/share/nginx/html/config.js
