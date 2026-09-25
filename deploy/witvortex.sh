#!/usr/bin/env bash
#
# witvortex.com — sunucuda deployment. Sunucuya ~/deploy/witvortex.sh olarak kopyalanır.
# GitHub Actions, authorized_keys'teki forced-command anahtarıyla bu betiği tetikler:
# GitHub'daki main çekilir ve web köküne senkronlanır.

set -euo pipefail

REPO="$HOME/repos/witvortex"
WEB=/var/www/witvortex.com

if [ ! -w "$WEB" ]; then
    echo "✗ $WEB yazılamıyor. Sunucuda bir kez çalıştırın:" >&2
    echo "    sudo chown -R faruk:www-data $WEB" >&2
    exit 1
fi

[ -d "$REPO/.git" ] || git clone --quiet https://github.com/biiomer/witvortex.git "$REPO"
git -C "$REPO" fetch --quiet origin main
git -C "$REPO" reset --quiet --hard origin/main

# Web köküne sadece site dosyaları gider; repo/altyapı dosyaları yayına çıkmaz.
rsync -rlt --delete --delete-excluded \
    --exclude=.git --exclude=.github --exclude=.gitignore \
    --exclude=deploy --exclude=docker --exclude=docker-compose.yml --exclude='*.md' \
    "$REPO/" "$WEB/"

echo "✓ witvortex.com yayında: $(git -C "$REPO" log -1 --format='%h %s')"
