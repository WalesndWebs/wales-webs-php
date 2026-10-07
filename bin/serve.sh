#!/usr/bin/env bash
set -euo pipefail
APP_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PORT="${PORT:-8000}"
if [[ ! "$PORT" =~ ^[0-9]+$ ]]; then echo "PORT must be numeric" >&2; exit 1; fi
RUNTIME="$(mktemp -d /tmp/wales-php.XXXXXX)"
FPM_PID=""
cleanup() {
  if [[ -n "$FPM_PID" ]]; then kill "$FPM_PID" 2>/dev/null || true; fi
  if [[ -f "$RUNTIME/nginx.pid" ]]; then kill "$(cat "$RUNTIME/nginx.pid")" 2>/dev/null || true; fi
  rm -rf "$RUNTIME"
}
trap cleanup EXIT INT TERM
cat > "$RUNTIME/fpm.conf" <<EOF
[global]
daemonize = no
error_log = /dev/stderr
[www]
listen = $RUNTIME/php.sock
pm = dynamic
pm.max_children = 6
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
pm.max_requests = 300
clear_env = no
catch_workers_output = yes
security.limit_extensions = .php
php_admin_flag[display_errors] = off
php_admin_flag[log_errors] = on
php_admin_value[error_log] = /dev/stderr
EOF
cat > "$RUNTIME/nginx.conf" <<EOF
daemon off;
pid $RUNTIME/nginx.pid;
error_log /dev/stderr warn;
worker_processes 1;
events { worker_connections 512; }
http {
  access_log off;
  client_body_temp_path $RUNTIME/body;
  fastcgi_temp_path $RUNTIME/fastcgi;
  types {
    text/html html; text/css css; application/javascript js;
    image/png png; image/jpeg jpg jpeg; image/svg+xml svg;
    image/webp webp; image/x-icon ico;
  }
  default_type application/octet-stream;
  server {
    listen 0.0.0.0:$PORT;
    server_name _;
    root $APP_ROOT/public;
    index index.php;
    client_max_body_size 128k;
    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location ^~ /assets/ { try_files \$uri =404; add_header Cache-Control "public, max-age=86400"; }
    location ~ /\. { return 404; }
    location = /index.php {
      fastcgi_pass unix:$RUNTIME/php.sock;
      fastcgi_param SCRIPT_FILENAME \$document_root/index.php;
      fastcgi_param SCRIPT_NAME /index.php;
      fastcgi_param DOCUMENT_ROOT \$document_root;
      fastcgi_param DOCUMENT_URI \$document_uri;
      fastcgi_param QUERY_STRING \$query_string;
      fastcgi_param REQUEST_METHOD \$request_method;
      fastcgi_param CONTENT_TYPE \$content_type;
      fastcgi_param CONTENT_LENGTH \$content_length;
      fastcgi_param REQUEST_URI \$request_uri;
      fastcgi_param SERVER_PROTOCOL \$server_protocol;
      fastcgi_param REMOTE_ADDR \$remote_addr;
      fastcgi_param REMOTE_PORT \$remote_port;
      fastcgi_param SERVER_NAME \$host;
      fastcgi_param SERVER_PORT \$server_port;
      fastcgi_param REDIRECT_STATUS 200;
      fastcgi_param HTTP_PROXY "";
    }
    location ~ \.php$ { return 404; }
  }
}
EOF
php-fpm --fpm-config "$RUNTIME/fpm.conf" --nodaemonize &
FPM_PID="$!"
for i in {1..50}; do
  [[ -S "$RUNTIME/php.sock" ]] && break
  if ! kill -0 "$FPM_PID" 2>/dev/null; then echo "PHP-FPM failed to start" >&2; exit 1; fi
  sleep .1
done
nginx -e /dev/stderr -t -c "$RUNTIME/nginx.conf" -p "$RUNTIME/"
nginx -e /dev/stderr -c "$RUNTIME/nginx.conf" -p "$RUNTIME/" &
wait "$!"