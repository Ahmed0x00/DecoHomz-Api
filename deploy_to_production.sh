#!/bin/bash
# Deploy to Production - DecoHomz API
# Usage: ./deploy_to_production.sh "commit message"
set -e

COMMIT_MSG="${1:-Deploy to production}"
REMOTE_PATH="/home/decohomz/htdocs/decohomz.com"

echo "=== Step 1: Git add, commit, and push ==="
git add .
git commit -m "$COMMIT_MSG"
git push origin main
echo "Pushed to origin/main"

echo ""
echo "=== Step 2: Pull on production server ==="
python3 remote_exec.py "cd $REMOTE_PATH && git pull origin main"

echo ""
echo "=== Step 3: Clear Laravel caches ==="
python3 remote_exec.py "cd $REMOTE_PATH && php artisan cache:clear && php artisan view:clear && php artisan config:clear && php artisan route:clear && php artisan optimize"

echo ""
echo "=== Deployment complete! ==="
