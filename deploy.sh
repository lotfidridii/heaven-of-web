#!/bin/bash

# Heaven of Web - Manual Deployment Script
# Usage: ./deploy.sh

echo "🚀 Starting deployment to Hostinger..."

# Build the React app
echo "📦 Building React application..."
npm run build

if [ $? -ne 0 ]; then
    echo "❌ Build failed! Please fix errors and try again."
    exit 1
fi

echo "✅ Build completed successfully!"

# Variables (update these with your Hostinger details)
HOST="your-hostinger-host.com"
USERNAME="your-username"
REMOTE_PATH="public_html"

echo "📤 Uploading files to Hostinger..."

# Upload build files to public_html
rsync -avz --progress --delete build/ $USERNAME@$HOST:$REMOTE_PATH/

# Upload backend files
scp upload-server.js package.json $USERNAME@$HOST:~/

echo "🔧 Installing dependencies on server..."
ssh $USERNAME@$HOST "cd ~/ && npm install --production"

echo "🔄 Restarting upload server..."
ssh $USERNAME@$HOST "pkill -f upload-server || true && nohup node upload-server.js > upload-server.log 2>&1 &"

echo "✅ Deployment completed successfully!"
echo "🌐 Your site should be available at: https://your-domain.com"
