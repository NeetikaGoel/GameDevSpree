#!/bin/bash

# go to project root from scripts folder
cd "$(dirname "$0")/.." || exit 1

# define pid file path
PID_FILE="run/cache-server.pid"

# if pid file does not exist then server is not running
if [ ! -f "$PID_FILE" ]; then
    echo "Cache server is not running"
    exit 0
fi

# read pid
PID=$(cat "$PID_FILE")

# if process does not exist then remove stale pid file
if ! kill -0 "$PID" 2>/dev/null; then
    echo "Process not found, removing stale PID file"
    rm -f "$PID_FILE"
    exit 0
fi

# send graceful stop signal
kill "$PID"

# wait for process to stop
sleep 1

# check if process is still running
if kill -0 "$PID" 2>/dev/null; then
    echo "Process still running, forcing stop"
    kill -9 "$PID" #do not do forceful shutdown, always try to do graceful shutdown, maybe -3 or something else
fi

# remove pid file
rm -f "$PID_FILE"

# show success msg
echo "Cache server stopped"