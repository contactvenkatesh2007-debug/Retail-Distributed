# ⚡ Quick Deploy Guide

## 🎯 Fast Track - Deploy in 5 Steps

### 1️⃣ Set GitHub Secrets (5 minutes)

Go to: Repository → Settings → Secrets and variables → Actions

Add these 4 secrets:
- `AWS_ACCESS_KEY_ID` - Your AWS access key
- `AWS_SECRET_ACCESS_KEY` - Your AWS secret key  
- `AWS_EC2_USER` - `ubuntu` (or `ec2-user`)
- `AWS_SSH_PRIVATE_KEY` - Full contents of your .pem file

### 2️⃣ Setup EC2 (10 minutes)

SSH into your EC2 instance and run:

```bash
# One-command setup
curl -fsSL https://raw.githubusercontent.com/your-repo/retail/main/scripts/setup-ec2.sh | bash

# Or manual setup
cd /var/www
sudo mkdir -p retail && sudo chown -R $USER:$USER retail
git clone <your-repo-url> retail
cd retail
cp .env.example .env
# Edit .env with your settings
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
```

### 3️⃣ Configure Nginx

```bash
sudo nano /etc/nginx/sites-available/retail
# Copy config from AWS_DEPLOYMENT.md
sudo ln -sf /etc/nginx/sites-available/retail /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl restart nginx
```

### 4️⃣ Push to Main Branch

```bash
git push origin main
```

### 5️⃣ Watch Deployment

1. Go to GitHub → Actions tab
2. Watch the deployment run automatically
3. Wait for ✅ green checkmark

**Done!** Your app is now live at `http://<your-ec2-ip>`

---

## 🔄 What Happens Next?

Every push to `main` branch automatically:
1. ✅ Runs tests
2. ✅ Builds application  
3. ✅ Deploys to EC2
4. ✅ Runs migrations
5. ✅ Restarts services

**No manual deployment needed!** 🎉

