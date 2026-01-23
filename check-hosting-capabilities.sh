#!/bin/bash

# Check Supervisor Availability on Shared Hosting
echo "🔍 Checking queue worker options for shared hosting..."

# Check if supervisor is available
if command -v supervisorctl &> /dev/null; then
    echo "✅ Supervisor is available"
    supervisorctl version
    
    # Check if we can access supervisor
    if supervisorctl status &> /dev/null; then
        echo "✅ Supervisor access: OK"
        supervisorctl status
    else
        echo "❌ Supervisor access: DENIED (may need different user or permissions)"
    fi
else
    echo "❌ Supervisor not available on this shared hosting"
fi

# Check if we can install packages
if command -v apt-get &> /dev/null; then
    echo "✅ Package manager available (may require sudo)"
else
    echo "❌ Package manager not available"
fi

# Check current user and permissions
echo "👤 Current user: $(whoami)"
echo "📁 Home directory: $HOME"
echo "🔧 Process ownership check:"
ps aux | grep -E "(queue|worker)" | head -5

# Check if cron is available
if command -v crontab &> /dev/null; then
    echo "✅ Crontab available"
    echo "📅 Current cron jobs:"
    crontab -l 2>/dev/null || echo "No cron jobs found"
else
    echo "❌ Crontab not available"
fi

# Check available process monitoring tools
echo "🛠️ Available process tools:"
for cmd in screen tmux nohup; do
    if command -v $cmd &> /dev/null; then
        echo "✅ $cmd available"
    else
        echo "❌ $cmd not available"
    fi
done