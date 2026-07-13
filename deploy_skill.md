# Deploy to Production Skill

Use this skill whenever the user asks to deploy, push to production, publish changes, or go live.

## Quick Deploy (one-liner)

```bash
./deploy_to_production.sh "your commit message"
```

## Manual Deploy Steps

### 1. Commit and push changes

```bash
cd /home/ahmex/Downloads/DecoHomz-api
git add .
git commit -m "description of changes"
git push origin main
```

### 2. Pull on production server

```bash
python3 remote_exec.py "cd /home/decohomz/htdocs/decohomz.com && git pull origin main"
```

### 3. Clear Laravel caches

```bash
python3 remote_exec.py "cd /home/decohomz/htdocs/decohomz.com && php artisan cache:clear && php artisan view:clear && php artisan config:clear && php artisan route:clear && php artisan optimize"
```

## Verify Deployment

```bash
curl -s -o /dev/null -w "%{http_code}" "https://decohomz.com/"
```

## Server Details

- **Host:** 23.95.10.234
- **SSH User:** decohomz
- **SSH Password:** IOMmKRarh45KYXQgRgvT
- **Web Root:** /home/decohomz/htdocs/decohomz.com
- **Git Branch:** main

## Notes

- The `remote_exec.py` script handles SSH connection automatically
- Use `--user root` with remote_exec.py if permission issues occur (root password: 082q3ZArJmNp4ShoK7)
- After deploying blade template changes, always clear view cache
- After deploying config changes, always clear config cache
- The `optimize` command re-caches routes, config, and views for production performance
