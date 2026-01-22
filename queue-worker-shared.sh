#!/bin/bash

# Shared Hosting Queue Worker Solution
# Works without supervisor or root access

APP_PATH="/home/weno/www"  # Updated for your hosting environment
WORKER_PID_FILE="$APP_PATH/storage/queue-worker.pid"
WORKER_LOG="$APP_PATH/storage/logs/queue-worker.log"
MAX_RUNTIME=3600  # 1 hour
SLEEP_TIME=3

cd "$APP_PATH"

# Function to start queue worker
start_worker() {
    echo "$(date): Starting queue worker..." >> "$WORKER_LOG"
    
    # Kill any existing worker
    if [ -f "$WORKER_PID_FILE" ]; then
        OLD_PID=$(cat "$WORKER_PID_FILE")
        if kill -0 "$OLD_PID" 2>/dev/null; then
            echo "$(date): Stopping old worker (PID: $OLD_PID)" >> "$WORKER_LOG"
            kill "$OLD_PID"
            sleep 2
        fi
        rm -f "$WORKER_PID_FILE"
    fi
    
    # Start new worker in background
    nohup php artisan queue:work database \
        --sleep="$SLEEP_TIME" \
        --tries=3 \
        --max-time="$MAX_RUNTIME" \
        --memory=256 \
        >> "$WORKER_LOG" 2>&1 &
    
    WORKER_PID=$!
    echo "$WORKER_PID" > "$WORKER_PID_FILE"
    echo "$(date): Started new worker (PID: $WORKER_PID)" >> "$WORKER_LOG"
}

# Function to check worker status
check_worker() {
    if [ -f "$WORKER_PID_FILE" ]; then
        WORKER_PID=$(cat "$WORKER_PID_FILE")
        if kill -0 "$WORKER_PID" 2>/dev/null; then
            echo "✅ Worker running (PID: $WORKER_PID)"
            return 0
        else
            echo "❌ Worker not running (stale PID file)"
            rm -f "$WORKER_PID_FILE"
            return 1
        fi
    else
        echo "❌ Worker not running (no PID file)"
        return 1
    fi
}

# Function to stop worker
stop_worker() {
    if [ -f "$WORKER_PID_FILE" ]; then
        WORKER_PID=$(cat "$WORKER_PID_FILE")
        if kill -0 "$WORKER_PID" 2>/dev/null; then
            echo "$(date): Stopping worker (PID: $WORKER_PID)" >> "$WORKER_LOG"
            kill "$WORKER_PID"
            rm -f "$WORKER_PID_FILE"
            echo "✅ Worker stopped"
        else
            echo "❌ Worker not running"
            rm -f "$WORKER_PID_FILE"
        fi
    else
        echo "❌ No worker running"
    fi
}

# Main logic based on command line argument
case "$1" in
    start)
        start_worker
        ;;
    stop)
        stop_worker
        ;;
    restart)
        stop_worker
        sleep 2
        start_worker
        ;;
    status)
        check_worker
        # Show recent jobs
        echo "📊 Queue Status:"
        php artisan queue:monitor default 2>/dev/null || echo "Could not check queue status"
        
        # Show recent logs
        echo "📝 Recent logs:"
        tail -10 "$WORKER_LOG" 2>/dev/null || echo "No logs found"
        ;;
    monitor)
        # Auto-restart if not running
        if ! check_worker; then
            echo "🔄 Worker not running, starting..."
            start_worker
        fi
        ;;
    *)
        echo "Usage: $0 {start|stop|restart|status|monitor}"
        echo "  start   - Start the queue worker"
        echo "  stop    - Stop the queue worker"
        echo "  restart - Restart the queue worker"
        echo "  status  - Check worker status and show queue info"
        echo "  monitor - Start worker if not running (use in cron)"
        exit 1
        ;;
esac