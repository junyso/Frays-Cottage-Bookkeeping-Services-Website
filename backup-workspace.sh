#!/bin/bash
# OpenClaw Workspace Backup Script
# Local → OneDrive backup (one-way sync)
# Author: Ingrid
# Usage: ./backup-workspace.sh

SOURCE="/Users/julianuseya/.openclaw/workspace/"
DEST="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/OpenClaw-Backup/"

echo "📦 Backing up OpenClaw workspace to OneDrive..."

# rsync options:
# -a: archive (preserve permissions, dates, etc.)
# -v: verbose (show what's happening)
# --delete: remove files in dest that don't exist in source
# --progress: show progress

rsync -av --progress "$SOURCE" "$DEST"

echo "✅ Backup complete!"
echo "📁 Source: $SOURCE"
echo "📁 Backup: $DEST"
echo "🕐 $(date)"
