# Setting Up GitHub Secrets for AWS Deployment

## 🔐 Required Secrets

To enable automatic deployment via GitHub Actions, you need to configure these secrets in your GitHub repository.

## 📋 Step-by-Step Guide

### 1. Access Repository Settings

1. Go to your GitHub repository
2. Click **Settings** (top menu)
3. In the left sidebar, click **Secrets and variables**
4. Click **Actions**
5. Click **New repository secret**

### 2. Add Required Secrets

Add each of these secrets one by one:

#### AWS_ACCESS_KEY_ID

**Name**: `AWS_ACCESS_KEY_ID`

**Value**: Your AWS Access Key ID

**How to get it**:
1. Log in to AWS Console
2. Go to IAM → Users → Your User
3. Go to Security Credentials tab
4. Click "Create access key"
5. Copy the Access Key ID

#### AWS_SECRET_ACCESS_KEY

**Name**: `AWS_SECRET_ACCESS_KEY`

**Value**: Your AWS Secret Access Key

**How to get it**:
- From the same screen where you created the access key
- Copy the Secret Access Key (you can only see it once!)

#### AWS_EC2_USER

**Name**: `AWS_EC2_USER`

**Value**: `ubuntu` (for Ubuntu instances) or `ec2-user` (for Amazon Linux)

#### AWS_SSH_PRIVATE_KEY

**Name**: `AWS_SSH_PRIVATE_KEY`

**Value**: The entire contents of your EC2 SSH private key file (.pem)

**How to get it**:

**Option A: If you have the key file**:
```bash
cat /path/to/your-key.pem
# Copy the entire output including -----BEGIN RSA PRIVATE KEY----- 
# and -----END RSA PRIVATE KEY-----
```

**Option B: If you don't have the key**:
1. Go to AWS EC2 Console
2. Click Key Pairs (in left sidebar)
3. Create new key pair or use existing one
4. Download the .pem file
5. Open it in a text editor and copy all contents

### 3. Verify Secrets

After adding all secrets, you should see:
- ✅ AWS_ACCESS_KEY_ID
- ✅ AWS_SECRET_ACCESS_KEY
- ✅ AWS_EC2_USER
- ✅ AWS_SSH_PRIVATE_KEY

## 🔑 Creating IAM User for CI/CD (Recommended)

Instead of using root credentials, create a dedicated IAM user:

### 1. Create IAM User

1. Go to AWS IAM Console
2. Click Users → Add users
3. Username: `github-actions-deploy`
4. Select "Programmatic access"
5. Click Next

### 2. Attach Policies

Attach these policies:
- `AmazonEC2FullAccess` (or create custom policy with EC2 describe permissions)
- `AmazonS3FullAccess` (if using S3)

Or create a custom policy:

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "ec2:DescribeInstances",
                "ec2:StartInstances",
                "ec2:StopInstances"
            ],
            "Resource": "*"
        }
    ]
}
```

### 3. Get Access Keys

1. After creating user, go to Security Credentials tab
2. Create access key
3. Copy Access Key ID and Secret Access Key
4. Use these in GitHub Secrets instead of root credentials

## ✅ Testing the Setup

1. Push a commit to `main` branch
2. Go to Actions tab in GitHub
3. Watch the deployment workflow
4. Check if it completes successfully

## 🐛 Troubleshooting

### Permission Denied (Publickey)

**Issue**: SSH connection fails

**Solution**:
- Verify `AWS_SSH_PRIVATE_KEY` contains the full key including headers
- Check that `AWS_EC2_USER` matches your instance OS
- Ensure the key has correct permissions on EC2 instance

### Access Denied

**Issue**: AWS API calls fail

**Solution**:
- Verify `AWS_ACCESS_KEY_ID` and `AWS_SECRET_ACCESS_KEY` are correct
- Check IAM user has necessary permissions
- Ensure region is set correctly

### Instance Not Found

**Issue**: Cannot find EC2 instance

**Solution**:
- Verify instance ID `i-0ed07d391b8a885e9` is correct
- Check instance is running
- Verify instance is in the correct region (us-east-1)

## 🔒 Security Best Practices

1. ✅ Use IAM user (not root account)
2. ✅ Grant minimum required permissions
3. ✅ Rotate access keys regularly
4. ✅ Never commit secrets to repository
5. ✅ Use environment-specific secrets if needed

## 📝 Quick Reference

```
AWS_ACCESS_KEY_ID        → Your AWS Access Key
AWS_SECRET_ACCESS_KEY    → Your AWS Secret Key  
AWS_EC2_USER            → ubuntu or ec2-user
AWS_SSH_PRIVATE_KEY     → Full contents of .pem file
```

---

**Note**: After adding secrets, any push to `main` branch will trigger automatic deployment!

