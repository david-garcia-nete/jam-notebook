#!/usr/bin/env bash
# Make executable before first use:
# chmod +x backup-db.sh

set -e

ENV_FILE=".env"
SAIL_BIN="./vendor/bin/sail"
BACKUP_DIR="backups"
TIMESTAMP="$(date +"%Y-%m-%d-%H%M%S")"

if [ ! -f "$ENV_FILE" ]; then
  echo "❌ Error: $ENV_FILE not found. Run this script from your Laravel project root." >&2
  exit 1
fi

if [ ! -x "$SAIL_BIN" ]; then
  echo "❌ Error: $SAIL_BIN not found or not executable. Install dependencies first (composer install)." >&2
  exit 1
fi

get_env_value() {
  local key="$1"
  local value

  value="$(sed -n "s/^${key}=//p" "$ENV_FILE" | tail -n 1)"
  value="${value%\"}"
  value="${value#\"}"
  value="${value%\'}"
  value="${value#\'}"

  printf '%s' "$value"
}

DB_HOST="$(get_env_value "DB_HOST")"
DB_PORT="$(get_env_value "DB_PORT")"
DB_DATABASE="$(get_env_value "DB_DATABASE")"
DB_USERNAME="$(get_env_value "DB_USERNAME")"
DB_PASSWORD="$(get_env_value "DB_PASSWORD")"

if [ -z "$DB_HOST" ] || [ -z "$DB_PORT" ] || [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ]; then
  echo "❌ Error: Missing one or more required DB settings in $ENV_FILE." >&2
  exit 1
fi

mkdir -p "$BACKUP_DIR"

BACKUP_FILE="$BACKUP_DIR/jam-notebook-$TIMESTAMP.sql"

echo "ℹ️  Starting backup for database: $DB_DATABASE"
echo "ℹ️  Writing to: $BACKUP_FILE"

if "$SAIL_BIN" exec -T mysql sh -c "MYSQL_PWD='${DB_PASSWORD//\'/\'\\\'\'}' mysqldump -h '$DB_HOST' -P '$DB_PORT' -u '$DB_USERNAME' '$DB_DATABASE'" > "$BACKUP_FILE"; then
  if [ ! -s "$BACKUP_FILE" ]; then
    echo "❌ Backup failed: output file is empty." >&2
    rm -f "$BACKUP_FILE"
    exit 1
  fi

  FILE_SIZE="$(du -h "$BACKUP_FILE" | cut -f1)"
  echo "✅ Backup completed successfully."
  echo "📦 File size: $FILE_SIZE"
  echo "📄 Backup file: $BACKUP_FILE"
else
  echo "❌ Backup failed." >&2
  rm -f "$BACKUP_FILE"
  exit 1
fi
